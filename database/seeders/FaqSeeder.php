<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'Are the courses self-paced or live?',
                'answer' => 'We offer both! Most courses are self-paced with weekly live mentorship sessions.',
                'sort_order' => 1,
            ],
            [
                'question' => 'Do I get a certificate upon completion?',
                'answer' => 'Yes, every graduate receives an industry-recognized certification verified by Astryx Academy.',
                'sort_order' => 2,
            ],
            [
                'question' => 'Is there a money-back guarantee?',
                'answer' => 'We offer a full refund within the first 7 days if you\'re not satisfied with the content.',
                'sort_order' => 3,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::create($faq);
        }
    }
}