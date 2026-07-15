<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Http\Requests\StoreLessonRequest;
use App\Services\LessonService;

class AdminLessonController extends Controller
{
    public function __construct(
        protected LessonService $lessonService,
    ) {}

    public function index(Course $course)
    {
        $lessons = $this->lessonService->getLessonsForCourse($course);
        return view('admin.lessons.index', compact('course', 'lessons'));
    }

    public function store(StoreLessonRequest $request, Course $course)
    {
        $this->lessonService->create($course, $request->validated());

        return redirect()->route('admin.courses.lessons.index', $course)
            ->with('success', 'Lesson added successfully!');
    }

    public function edit(Course $course, Lesson $lesson)
    {
        return view('admin.lessons.edit', compact('course', 'lesson'));
    }

    public function update(StoreLessonRequest $request, Course $course, Lesson $lesson)
    {
        $this->lessonService->update($lesson, $request->validated());

        return redirect()->route('admin.courses.lessons.index', $course)
            ->with('success', 'Lesson updated successfully!');
    }

    public function destroy(Course $course, Lesson $lesson)
    {
        $this->lessonService->delete($lesson);

        return redirect()->route('admin.courses.lessons.index', $course)
            ->with('success', 'Lesson deleted successfully!');
    }
}
