<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Ahmed Nasim', 'email' => 'ahmed@example.com'],
            ['name' => 'John Doe', 'email' => 'john@example.com'],
            ['name' => 'Sarah Khan', 'email' => 'sarah@example.com'],
            ['name' => 'Ali Raza', 'email' => 'ali@example.com'],
            ['name' => 'Emma Watson', 'email' => 'emma@example.com'],
            ['name' => 'Michael Lee', 'email' => 'michael@example.com'],
            ['name' => 'Sophia Noor', 'email' => 'sophia@example.com'],
            ['name' => 'Daniel Smith', 'email' => 'daniel@example.com'],
            ['name' => 'Zain Ahmed', 'email' => 'zain@example.com'],
            ['name' => 'Olivia Brown', 'email' => 'olivia@example.com'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('12345678'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}