<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $name = env('ADMIN_NAME', 'Administrator');
        $password = env('ADMIN_PASSWORD');

        if (!$email) {
            $this->command->error('ADMIN_EMAIL is not set in .env file. Skipping admin user creation.');
            return;
        }

        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password ?: 'password'),
                'role' => 'admin',
            ]
        );

        $this->command->info('Admin user created/verified successfully via environment variables.');
    }
}