<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Admin User',
                'password' => Hash::make('Password123!'),
                'role' => 'admin',
                'avatar' => null,
                'bio' => 'Administrator account'
            ]
        );

        // Teacher 1
        User::updateOrCreate(
            ['email' => 'teacher1@example.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Teacher User 1',
                'password' => Hash::make('Password123!'),
                'role' => 'teacher',
                'avatar' => null,
                'bio' => 'Teacher account 1'
            ]
        );

        // Teacher 2
        User::updateOrCreate(
            ['email' => 'teacher2@example.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Teacher User 2',
                'password' => Hash::make('Password123!'),
                'role' => 'teacher',
                'avatar' => null,
                'bio' => 'Teacher account 2'
            ]
        );

        // Student 1
        User::updateOrCreate(
            ['email' => 'student1@example.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Student User 1',
                'password' => Hash::make('Password123!'),
                'role' => 'student',
                'avatar' => null,
                'bio' => 'Student account 1'
            ]
        );

        // Student 2
        User::updateOrCreate(
            ['email' => 'student2@example.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Student User 2',
                'password' => Hash::make('Password123!'),
                'role' => 'student',
                'avatar' => null,
                'bio' => 'Student account 2'
            ]
        );
    }
}
