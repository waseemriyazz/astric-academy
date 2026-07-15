<?php

namespace App\Services;

use App\Models\User;
use App\Models\Course;

class EnrollmentService
{
    public function isEnrolled(User $user, Course $course): bool
    {
        return $user->courses()->where('courses.id', $course->id)->exists();
    }

    public function verifyLessonBelongsToCourse(Course $course, $lesson): bool
    {
        return $lesson->course_id === $course->id;
    }
}