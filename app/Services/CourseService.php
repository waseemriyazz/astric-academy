<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Support\Facades\Log;

class CourseService
{
    public function getAllPaginated(int $perPage = 10)
    {
        return Course::latest()->paginate($perPage);
    }

    public function create(array $data): Course
    {
        $course = Course::create($data);

        Log::info('Admin created course', [
            'admin_id' => auth()->id(),
            'course_id' => $course->id,
            'title' => $course->title,
            'price' => $course->price,
        ]);

        return $course;
    }

    public function update(Course $course, array $data): Course
    {
        $course->update($data);

        Log::info('Admin updated course', [
            'admin_id' => auth()->id(),
            'course_id' => $course->id,
            'title' => $course->title,
        ]);

        return $course;
    }

    public function delete(Course $course): void
    {
        $courseId = $course->id;
        $courseTitle = $course->title;
        $course->delete();

        Log::info('Admin deleted course', [
            'admin_id' => auth()->id(),
            'course_id' => $courseId,
            'title' => $courseTitle,
        ]);
    }
}