<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin / Owner User
        User::updateOrCreate(
            ['email' => 'adminpamtechno@gmail.com'],
            [
                'name' => 'Admin Pam Techno',
                'username' => 'admin',
                'password' => Hash::make('adminspirit45'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Cashier User
        User::updateOrCreate(
            ['email' => 'jaeyi@gmail.com'],
            [
                'name' => 'Kasir Jaeyi',
                'username' => 'jaeyi',
                'password' => Hash::make('jaeyispirit45'),
                'role' => 'kasir',
                'email_verified_at' => now(),
            ]
        );
    }
}
