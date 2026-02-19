<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed a default admin user for SmartSchool.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@smartschool.com'],
            [
                'name'     => 'Admin SmartSchool',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );
    }
}
