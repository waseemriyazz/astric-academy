<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Support\Facades\Log;

class LessonService
{
    public function getLessonsForCourse(Course $course)
    {
        return $course->lessons()->orderBy('order')->get();
    }

    public function create(Course $course, array $data): Lesson
    {
        // Auto-assign next order if not provided or set to 0
        if (empty($data['order']) || (int) $data['order'] === 0) {
            $data['order'] = ($course->lessons()->max('order') ?? 0) + 1;
        }

        $lesson = $course->lessons()->create($data);

        Log::info('Admin created lesson', [
            'admin_id' => auth()->id(),
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'title' => $lesson->title,
        ]);

        // Create quiz if quiz data is provided
        if (!empty($data['quiz_question'])) {
            $lesson->quiz()->create([
                'question' => $data['quiz_question'],
                'option_a' => $data['quiz_option_a'],
                'option_b' => $data['quiz_option_b'],
                'option_c' => $data['quiz_option_c'],
                'option_d' => $data['quiz_option_d'],
                'correct_answer' => $data['quiz_correct_answer'],
            ]);
        }

        return $lesson;
    }

    public function update(Lesson $lesson, array $data): Lesson
    {
        if (empty($data['order']) || (int) $data['order'] === 0) {
            $data['order'] = $lesson->order;
        }

        $lesson->update($data);

        Log::info('Admin updated lesson', [
            'admin_id' => auth()->id(),
            'course_id' => $lesson->course_id,
            'lesson_id' => $lesson->id,
            'title' => $lesson->title,
        ]);

        // Update or create quiz
        if (!empty($data['quiz_question'])) {
            $lesson->quiz()->updateOrCreate(
                ['lesson_id' => $lesson->id],
                [
                    'question' => $data['quiz_question'],
                    'option_a' => $data['quiz_option_a'],
                    'option_b' => $data['quiz_option_b'],
                    'option_c' => $data['quiz_option_c'],
                    'option_d' => $data['quiz_option_d'],
                    'correct_answer' => $data['quiz_correct_answer'],
                ]
            );
        } else {
            // If quiz question is empty, delete any existing quiz
            $lesson->quiz()->delete();
        }

        return $lesson;
    }

    public function delete(Lesson $lesson): void
    {
        $lessonId = $lesson->id;
        $lessonTitle = $lesson->title;
        $lesson->delete();

        Log::info('Admin deleted lesson', [
            'admin_id' => auth()->id(),
            'course_id' => $lesson->course_id,
            'lesson_id' => $lessonId,
            'title' => $lessonTitle,
        ]);
    }
}