<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'James Wilson',
                'role' => 'Frontend Developer at Google',
                'content' => 'This course was the turning point in my career. The curriculum is incredibly practical and the mentorship is unmatched.',
                'image_url' => 'https://i.pravatar.cc/150?u=james',
                'sort_order' => 1,
            ],
            [
                'name' => 'Emily Chen',
                'role' => 'UI Designer at Airbnb',
                'content' => 'I went from zero design knowledge to a professional portfolio in just 10 weeks. Best investment I ever made.',
                'image_url' => 'https://i.pravatar.cc/150?u=emily',
                'sort_order' => 2,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::create($testimonial);
        }
    }
}