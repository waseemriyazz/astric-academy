<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Http\Requests\StoreCourseRequest;
use App\Services\CourseService;

class AdminCourseController extends Controller
{
    public function __construct(
        protected CourseService $courseService,
    ) {}

    public function index()
    {
        $courses = $this->courseService->getAllPaginated();
        return view('admin.courses.index', compact('courses'));
    }

    public function create()
    {
        return view('admin.courses.create');
    }

    public function store(StoreCourseRequest $request)
    {
        $course = $this->courseService->create($request->validated());

        return redirect()->route('admin.courses.lessons.index', $course)
            ->with('success', 'Course created! Now add your lessons.');
    }

    public function edit(Course $course)
    {
        return view('admin.courses.edit', compact('course'));
    }

    public function update(StoreCourseRequest $request, Course $course)
    {
        $this->courseService->update($course, $request->validated());

        return redirect()->route('admin.courses.index')->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $this->courseService->delete($course);

        return redirect()->route('admin.courses.index')->with('success', 'Course deleted successfully.');
    }
}
