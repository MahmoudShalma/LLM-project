<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::firstOrCreate(
            ['email' => 'admin@minilms.com'],
            [
                'name'              => 'Admin User',
                'password'          => Hash::make('password'),
                'is_admin'          => true,
                'email_verified_at' => now(),
            ]
        );

        // Demo student
        User::firstOrCreate(
            ['email' => 'student@minilms.com'],
            [
                'name'              => 'Demo Student',
                'password'          => Hash::make('password'),
                'is_admin'          => false,
                'email_verified_at' => now(),
            ]
        );

        // Additional random students
        User::factory()->count(10)->create();
    }
}
