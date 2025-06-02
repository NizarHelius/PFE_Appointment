<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        // Create 7 employees
        for ($i = 1; $i <= 7; $i++) {
            $user = User::create([
                'name' => "Employee $i",
                'email' => "employee$i@example.com",
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            $user->assignRole('employee');

            Employee::create([
                'user_id' => $user->id,
                'phone' => "06" . rand(10000000, 99999999),
                'address' => "Address for Employee $i",
                'status' => 'active',
            ]);
        }

        // Create 12 users (subscribers)
        for ($i = 1; $i <= 12; $i++) {
            $user = User::create([
                'name' => "User $i",
                'email' => "user$i@example.com",
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            $user->assignRole('subscriber');
        }
    }
}
