<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin user
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@skillstryx.com',
            'password' => Hash::make('SuperAdmin@123'),
            'role' => 'admin',
        ]);

        $this->command->info('Super user created successfully!');
        $this->command->info('Email: superadmin@skillstryx.com');
        $this->command->info('Password: SuperAdmin@123');
    }
}