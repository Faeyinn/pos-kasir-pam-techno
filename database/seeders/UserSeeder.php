<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{

    public function run(): void
    {

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
