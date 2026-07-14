<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL')],
            [
                'name' => env('ADMIN_NAME', 'Administrator'),
                'password' => Hash::make(env('ADMIN_PASSWORD')),
                'role' => 'admin',
            ]
        );

        $this->command->info('Admin user created/verified successfully via environment variables.');
    }
}