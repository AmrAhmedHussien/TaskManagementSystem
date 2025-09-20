<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'is_manager' => true
        ]);

        User::create([
            'name' => 'Regular User 1',
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
            'is_manager' => false
        ]);

        User::create([
            'name' => 'Regular User 2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
            'is_manager' => false
        ]);

        User::create([
            'name' => 'Regular User 3',
            'email' => 'user3@example.com',
            'password' => Hash::make('password'),
            'is_manager' => false
        ]);
    }
}
