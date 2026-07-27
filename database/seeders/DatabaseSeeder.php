<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            CourseSeeder::class,
            PlanSeeder::class,
            TestimonialSeeder::class,
            FaqSeeder::class,
            PaymentGatewayConfigSeeder::class,
        ]);
    }
}