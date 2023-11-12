<?php

use Tests\TestCase;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testStoreEmployeeWithInvalidData()
    {
        $response = $this->post('/api/employees', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'errors',
                'validator',
            ]);
    }

    public function testStoreSeniorEmployeeWithValidData()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'Pass@123',
            'password_confirmation' => 'Pass@123',
            'role' => 'user',
            'senior' => true
        ];

        $response = $this->post('/api/employees', $data);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Employee Created Successfully!',
            ]);
    }

    public function testStoreEmployeeWithValidData()
    {
        $supervisor = Employee::factory()->create();

        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'Pass@123',
            'password_confirmation' => 'Pass@123',
            'role' => 'user',
            'senior' => false,
            'supervisor_id' => $supervisor->id
        ];

        $response = $this->post('/api/employees', $data);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Employee Created Successfully!',
            ]);
    }


    public function testShowEmployeeNotFound()
    {
        $response = $this->get('/api/employees/999');

        $response->assertStatus(404);
    }

    public function testShowEmployeeFound()
    {
        $employee = Employee::factory()->create();

        $response = $this->get('/api/employees/' . $employee->id);

        $response->assertStatus(200)
            ->assertJson($employee->toArray());
    }

    public function testUpdateEmployeeNotFound()
    {
        $response = $this->put('/api/employees/999'); // Assuming '999' doesn't exist

        $response->assertStatus(404);
    }

    public function testUpdateSeniorEmployeeWithValidData()
    {
        $employee = Employee::factory()->create();

        $data = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'user' => ['role' => 'admin'],
            'senior' => true
        ];

        $response = $this->put('/api/employees/' . $employee->id, $data);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Employee updated successfully!',
            ]);
    }

    public function testUpdateEmployeeWithValidData()
    {
        $supervisor = Employee::factory()->create();
        $employee = Employee::factory()->nonSenior($supervisor->id)->create();

        $data = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'user' => ['role' => 'admin'],
            'senior' => false,
            'supervisor_id' => $supervisor->id
        ];

        $response = $this->put('/api/employees/' . $employee->id, $data);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Employee updated successfully!',
            ]);
    }

    public function testUpdateEmployeeFromRegularToSeniorWithValidData()
    {
        $supervisor = Employee::factory()->create();
        $employee = Employee::factory()->nonSenior($supervisor->id)->create();

        $data = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'user' => ['role' => 'admin'],
            'senior' => true,
            'supervisor_id' => null
        ];

        $response = $this->put('/api/employees/' . $employee->id, $data);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Employee updated successfully!',
            ]);
    }

    public function testDeleteEmployeeNotFound()
    {
        $response = $this->delete('/api/employees/999'); // Assuming '999' doesn't exist

        $response->assertStatus(404);
    }

    public function testDeleteEmployee()
    {
        $employee = Employee::factory()->create();
        $one = Employee::factory()->nonSenior($employee->id)->create();

        $response = $this->delete('/api/employees/' . $one->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Deleted successfully!',
            ]);
    }

    public function testDeleteSeniorEmployee()
    {
        $employee = Employee::factory()->create();

        $response = $this->delete('/api/employees/' . $employee->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Can not delete Senior Supervisor, set a different one first.',
            ]);
    }

    public function testGenerateEmployeeChart()
    {
        $rootEmployee = Employee::factory()->create();
        $one = Employee::factory()->nonSenior($rootEmployee->id)->create();
        $two = Employee::factory()->nonSenior($rootEmployee->id)->create();
        $three = Employee::factory()->nonSenior($rootEmployee->id)->create();
        $four = Employee::factory()->nonSenior($three->id)->create();

        $response = $this->get('/api/chart');

        $response->assertStatus(200)
            ->assertJson([
                "message" => 'Organizational Chart Generated.',
                'data' => [
                    $rootEmployee->name => [
                        [$one->name => []], [$two->name => []], [$three->name => [[$four->name => []]]]
                    ]
                ]
            ]);
    }
}
