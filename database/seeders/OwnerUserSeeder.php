<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class OwnerUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat akun Owner (Role: master)
        User::updateOrCreate(
            ['email' => 'hanaviza13@gmail.com'], // Email unik sebagai identifier
            [
                'nama' => 'Owner Pam Techno',
                'username' => 'owner_pam',
                'password' => Hash::make('owner123'), // Password untuk testing
                'role' => 'master',
                'email_verified_at' => now(),
            ]
        );
    }
}
