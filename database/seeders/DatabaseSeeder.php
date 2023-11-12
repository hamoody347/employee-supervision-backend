<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


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

        // $rootEmployee = \App\Models\Employee::factory()->create();
        // $one = \App\Models\Employee::factory()->nonSenior($rootEmployee->id)->create();
        // $two = \App\Models\Employee::factory()->nonSenior($rootEmployee->id)->create();
        // $three = \App\Models\Employee::factory()->nonSenior($rootEmployee->id)->create();
        // $four = \App\Models\Employee::factory()->nonSenior($three->id)->create();

        $admin = \App\Models\User::create([
            "name" => "Admin",
            "email" => "admin@example.com",
            "password" => Hash::make('password'),
            "role" => "admin",

        ]);
        \App\Models\Employee::create([
            "name" => $admin->name,
            "email" => $admin->email,
            "senior" => true,
            "user_id" => $admin->id
        ]);

        $user = \App\Models\User::create([
            "name" => "User",
            "email" => "user@example.com",
            "password" => Hash::make('password'),
            "role" => "user",

        ]);
        \App\Models\Employee::create([
            "name" => $user->name,
            "email" => $user->email,
            "senior" => true,
            "user_id" => $user->id
        ]);
    }
}
