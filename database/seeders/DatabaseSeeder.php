<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@astryxacademy.com',
            'role' => 'admin',
        ]);

        // Call SuperUserSeeder to create the super admin user
        $this->call(SuperUserSeeder::class);

        Course::create([
            'title' => 'Web Development Bootcamp',
            'description' => 'Learn full-stack web development from scratch.',
            'price' => 49.99
        ]);

        Course::create([
            'title' => 'UI/UX Design Masterclass',
            'description' => 'Master Figma and design principles.',
            'price' => 29.99
        ]);
        
        Course::create([
            'title' => 'Data Science for Beginners',
            'description' => 'Introduction to Python and Data Science.',
            'price' => 59.99
        ]);
    }
}
