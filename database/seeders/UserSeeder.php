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
            ['username' => 'master'],
            [
                'name' => 'Master Owner',
                'email' => 'masterpam@gmail.com',
                'password' => Hash::make('masterspirit45'),
                'role' => 'master',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Pam Techno',
                'email' => 'adminpam@gmail.com',
                'password' => Hash::make('adminspirit45'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['username' => 'kasir'],
            [
                'name' => 'Kasir Staff',
                'email' => 'kasirpam@gmail.com',
                'password' => Hash::make('kasirspirit45'),
                'role' => 'kasir',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['username' => 'jaeyi'],
            [
                'name' => 'Jaeyi',
                'email' => 'jaeyi@gmail.com',
                'password' => Hash::make('jaeyispirit45'),
                'role' => 'kasir',
                'email_verified_at' => now(),
            ]
        );
    }
}
