<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'summary' => 'nullable|string',
            'youtube_url' => 'nullable|string',
            'vimeo_url' => 'nullable|string',
            'duration' => 'nullable|string|max:50',
            'order' => 'nullable|integer',
            'quiz_question' => 'nullable|string',
            'quiz_option_a' => 'nullable|string',
            'quiz_option_b' => 'nullable|string',
            'quiz_option_c' => 'nullable|string',
            'quiz_option_d' => 'nullable|string',
            'quiz_correct_answer' => 'nullable|in:a,b,c,d',
        ];
    }
}