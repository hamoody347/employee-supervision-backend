<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    function index()
    {
        $employees = Employee::with(['supervisor', 'user'])->orderByRaw('-senior ASC')->get();

        return response()->json($employees);
    }

    function employeeSupervisor()
    {

        $employees = Employee::with(['supervisor'])->orderByRaw('-senior ASC')->get();

        foreach ($employees as $employee) {
            $subordinates = $employee->getNestedSubordinates($employee);
            $possibleSupervisors = Employee::whereNotIn('id', $subordinates)->get();
            $employee['supervisors'] = $possibleSupervisors;
        }
        return response()->json($employees);
    }

    function show($id)
    {
        try {
            $employee = Employee::with(['supervisor', 'user'])->findOrFail($id);

            return response()->json($employee);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['errors' => 'Employee not found!', 'back' => true], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed', 'error' => $e->getMessage()], 500);
        }
    }

    function store(Request $request)
    {
        try {

            $data = $request->validate([
                'name' => 'required|string|unique:employees',
                'email' => 'required|email|unique:employees',
                'password' => [
                    'min:8',
                    'string',
                    'required',
                    'confirmed', // Requires a matching password_confirmation field
                ],
                'role' => 'required|in:admin,user',
            ]);

            $hashed = Hash::make($data['password']);
            $data['password'] = $hashed;

            $user = User::create($data);
            $data['user_id'] = $user->id;

            $employee = Employee::create($data);

            if ($request->senior) {
                $employee->setSenior();
            } elseif ($request->supervisor_id) {
                try {
                    $employee->senior = false;
                    $employee->setSupervisor($request->supervisor_id);
                } catch (\Exception $e) {
                    return response()->json(['errors' => $e->getMessage(), 'validator' => true], 422);
                }
            } else {
                return response()->json(['errors' => ['supervisor' => 'Supervisor is required.'], 'validator' => true], 422);
            }

            return response()->json(['message' => 'Employee Created Successfully!'], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors(), 'validator' => true], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed', 'error' => $e->getMessage()], 500);
        }
    }

    function update(Request $request)
    {
        try {

            $employee = Employee::findOrFail($request->id);
            $user = $employee->user();

            $data = $request->validate([
                'name' => 'required|string|unique:employees,email,' . $employee->id,
                'email' => 'required|email|unique:employees,email,' . $employee->id,
                'password' => [
                    'min:8',
                    'string',
                    'confirmed', // Requires a matching password_confirmation field
                ],
            ]);

            $data['role'] = $request['user']['role'];
            $user->update($data);
            $employee->update($data);

            if ($request->senior) {
                $employee->setSenior();
            } elseif ($request->supervisor_id) {
                try {
                    $employee->senior = false;
                    $employee->setSupervisor($request->supervisor_id);
                } catch (\Exception $e) {
                    return response()->json(['errors' => $e->getMessage(), 'validator' => true], 422);
                }
            } else {
                return response()->json(['errors' => ['supervisor' => 'Supervisor is required.'], 'validator' => true, 'data' => $request->senior], 422);
            }

            return response()->json(['message' => 'Employee updated successfully!'], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors(), 'validator' => true], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['errors' => 'Employee not found!', 'back' => true], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed', 'error' => $e->getMessage()], 500);
        }
    }

    function delete($id)
    {
        try {

            $employee = Employee::findOrFail($id);

            if ($employee->senior) {
                return response()->json(['message' => 'Can not delete Senior Supervisor, set a different one first.']);
            }

            $subordinates = Employee::where('supervisor_id', $employee->id);

            $user = $employee->user();
            $user->delete();

            return response()->json(['message' => 'Deleted successfully!', 'sub' => $subordinates], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['errors' => 'Couldn\'t delete, employee not found', 'back' => false], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed', 'error' => $e->getMessage()], 500);
        }
    }

    function generateEmployeeChart()
    {
        try {
            $chart = Employee::getEmployeeChain();

            return response()->json(['message' => 'Organizational Chart Generated.', 'data' => $chart]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to generate chart', 'error' => $e->getMessage()], 500);
        }
    }

    function setSupervisor(Request $request)
    {
        try {

            $employee = Employee::findOrFail($request->id);

            $employee->setSupervisor($request->supervisor_id);
            $employee->save();

            return response()->json(['message' => 'Employee supervisor updated successfully!'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['errors' => 'Employee not found!', 'back' => false], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed', 'error' => $e->getMessage()], 500);
        }
    }

    function getSupervisors($id)
    {
        try {
            $employee = Employee::find($id);
            $subordinates = $employee->getNestedSubordinates($employee);

            $possibleSupervisors = Employee::whereNotIn('id', $subordinates)->get();

            return response()->json($possibleSupervisors, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed', 'error' => $e->getMessage()], 500);
        }
    }
}
