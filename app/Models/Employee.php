<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'user_id',
    ];

    protected $guarded = [
        'senior',
        'supervisor_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'supervisor_id');
    }

    public function setSenior()
    {
        try {
            $oldSeniorEmployee = Employee::where('senior', true)->first();
        } catch (\Exception $e) {
            throw new \Exception("Block 1");
        }
        try {
            if ($oldSeniorEmployee) {
                $oldSeniorEmployee->senior = false;
                $oldSeniorEmployee->supervisor_id = $this->id;
                $oldSeniorEmployee->save();
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        try {
            $this->senior = true;
            $this->supervisor_id = null;
            $this->save();
        } catch (\Exception $e) {
            throw new \Exception("Block 3");
        }
    }

    public function setSupervisor(int $id)
    {
        if ($this->id === $id) {
            throw new \Exception("Cannot set supervisor as the same as the employee");
        }

        // Check for circular reference
        $newSupervisor = Employee::find($id);

        if ($newSupervisor) {
            // Check if setting the supervisor creates a circular reference
            $currentEmployee = $newSupervisor;

            while ($currentEmployee) {
                if ($currentEmployee->id === $this->id) {
                    throw new \Exception("Circular supervision detected. Cannot set supervisor.");
                }

                $currentEmployee = Employee::find($currentEmployee->supervisor_id);
            }
        }

        // If no circular reference, set the supervisor
        $this->supervisor_id = $id;
        $this->save();
    }

    public static function getEmployeeChain($root = true, $employee = null)
    {
        if ($root) {
            $employee = Employee::where('senior', true)->first();
        } elseif (!$employee) {
            return []; // No employee specified and not in the root mode, return an empty array
        }

        $chain = [];
        $subordinates = $employee->subordinates;

        foreach ($subordinates as $subordinate) {
            if ($root) {
                $chain[$employee->name][] = [$subordinate->name => self::getEmployeeChain(false, $subordinate)];
            } else {
                $chain[] = [$subordinate->name => self::getEmployeeChain(false, $subordinate)];
            }
        }

        return $chain ?: []; // If $chain is falsy (has no subordinates), return an empty array
    }

    public function getNestedSubordinates($employee, $subordinates = [])
    {
        $employeeId = $employee->id;

        // Add the current employee to the list of subordinates
        $subordinates[] = $employeeId;

        // Get all direct subordinates of the given employee
        $empSubordinates = $employee->subordinates;

        foreach ($empSubordinates as $subordinate) {
            // If the subordinate is not in the list, add it and recursively get its subordinates
            if (!in_array($subordinate->id, $subordinates)) {
                $subordinates = $this->getNestedSubordinates($subordinate, $subordinates);
            }
        }

        return $subordinates;
    }
}
