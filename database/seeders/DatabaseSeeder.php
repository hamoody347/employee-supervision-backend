<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $rootEmployee = \App\Models\Employee::factory()->create();
        $one = \App\Models\Employee::factory()->nonSenior($rootEmployee->id)->create();
        $two = \App\Models\Employee::factory()->nonSenior($rootEmployee->id)->create();
        $three = \App\Models\Employee::factory()->nonSenior($rootEmployee->id)->create();
        $four = \App\Models\Employee::factory()->nonSenior($three->id)->create();
    }
}
