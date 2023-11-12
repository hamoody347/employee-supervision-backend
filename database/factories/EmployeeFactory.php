<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->name,
            'email' => $this->faker->unique()->safeEmail,
            'senior' => true,
            'user_id' => User::factory()->create()->id,
            'supervisor_id' => null,
        ];
    }

    // State to define a non-senior employee
    public function nonSenior($supervisorId)
    {
        return $this->state(function () use ($supervisorId) {
            return [
                'senior' => false,
                'supervisor_id' => $supervisorId,
            ];
        });
    }
}
