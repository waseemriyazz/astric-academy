<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $courses = Course::all();

        if ($courses->isEmpty()) {
            $this->command->warn('No courses found. Run CourseSeeder first.');
            return;
        }

        $tiers = [
            [
                'tier_name' => 'Basic',
                'description' => 'Get started with the fundamentals. Perfect for beginners who want to build a strong foundation.',
                'price_multiplier' => 0.25,
                'duration' => '2 Months',
                'sort_order' => 1,
                'features' => [
                    'Core module access',
                    'Basic learning materials',
                    'Email support',
                    'Certificate of completion',
                ],
                'includes' => [
                    'All core lessons',
                    'Downloadable resources',
                    'Community access',
                ],
            ],
            [
                'tier_name' => 'Intermediate',
                'description' => 'Deepen your knowledge with advanced concepts and hands-on projects. Ideal for learners with some experience.',
                'price_multiplier' => 0.50,
                'duration' => '4 Months',
                'sort_order' => 2,
                'features' => [
                    'Everything in Basic',
                    'Advanced modules',
                    'Practical projects',
                    'Priority email support',
                    'Quiz & assessments',
                ],
                'includes' => [
                    'All Basic features',
                    'Project-based learning',
                    'Progress tracking',
                    'Study group access',
                ],
            ],
            [
                'tier_name' => 'Advanced',
                'description' => 'Master complex topics and work on real-world scenarios. Designed for those aiming for professional expertise.',
                'price_multiplier' => 0.75,
                'duration' => '6 Months',
                'sort_order' => 3,
                'features' => [
                    'Everything in Intermediate',
                    'Expert-level modules',
                    'Real-world case studies',
                    '1-on-1 mentoring sessions',
                    'Portfolio projects',
                ],
                'includes' => [
                    'All Intermediate features',
                    'Mentorship program',
                    'Industry case studies',
                    'Portfolio development',
                    'Career guidance',
                ],
            ],
            [
                'tier_name' => 'Pro',
                'description' => 'The complete experience. Get unlimited access, personalized coaching, and career-ready certification.',
                'price_multiplier' => 1.0,
                'duration' => '12 Months',
                'sort_order' => 4,
                'features' => [
                    'Everything in Advanced',
                    'All modules unlimited access',
                    'Personalized coaching',
                    'Live weekly sessions',
                    'Job placement assistance',
                    'Lifetime access & updates',
                ],
                'includes' => [
                    'All Advanced features',
                    'Personal coach',
                    'Live masterclasses',
                    'Resume & interview prep',
                    'Priority support 24/7',
                    'Alumni network access',
                ],
            ],
        ];

        $createdCount = 0;

        foreach ($courses as $course) {
            foreach ($tiers as $tier) {
                $price = round($course->price_max * $tier['price_multiplier'], 2);

                // Ensure minimum price of $10
                if ($price < 10) {
                    $price = 10;
                }

                $slug = Str::slug($tier['tier_name'] . '-' . $course->id);

                Plan::create([
                    'course_id' => $course->id,
                    'tier_name' => $tier['tier_name'],
                    'slug' => $slug,
                    'description' => $tier['description'],
                    'price' => $price,
                    'features' => $tier['features'],
                    'duration' => $tier['duration'],
                    'includes' => $tier['includes'],
                    'sort_order' => $tier['sort_order'],
                    'is_active' => true,
                ]);

                $createdCount++;
            }
        }

        $this->command->info("Created {$createdCount} plans for {$courses->count()} courses.");
    }
}