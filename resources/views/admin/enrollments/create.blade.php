@extends('layouts.admin')

@section('header', 'Add Enrollment')

@section('content')
<div class="max-w-4xl bg-white rounded-2xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 p-8">
    <div class="flex items-center justify-between mb-8 pb-6 border-b border-gray-100">
        <div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">Select Details</h3>
            <p class="text-sm text-gray-500">Pick the student and the course they should gain access to.</p>
        </div>
        <a href="{{ route('admin.enrollments.index') }}" class="text-sm font-medium text-gray-500 hover:text-blue-600 transition flex items-center gap-1">
            <i class="fas fa-arrow-left"></i> Back to Enrollments
        </a>
    </div>
    
    <form action="{{ route('admin.enrollments.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Select Student *</label>
            <select name="user_id" required class="w-full rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                <option value="">-- Select a registered student --</option>
                @foreach($students as $student)
                    <option value="{{ $student->id }}" {{ old('user_id') == $student->id ? 'selected' : '' }}>
                        {{ $student->name }} ({{ $student->email }})
                    </option>
                @endforeach
            </select>
            @error('user_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Select Course *</label>
            <select name="course_id" required class="w-full rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                <option value="">-- Choose a course --</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                        {{ $course->title }}
                    </option>
                @endforeach
            </select>
            @error('course_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        
        <div class="bg-blue-50 rounded-2xl p-4 border border-blue-100 flex gap-3">
            <div class="mt-0.5 text-blue-500">
                <i class="fas fa-info-circle"></i>
            </div>
            <div>
                <h4 class="text-sm font-bold text-blue-900 mb-1">Instant Access</h4>
                <p class="text-xs text-blue-700/80 leading-relaxed">By manually enrolling a student, they bypass any payment gateways and gain immediate access to the course materials.</p>
            </div>
        </div>

        <div class="pt-6 border-t border-gray-100 flex items-center justify-end gap-3 mt-8">
            <a href="{{ route('admin.enrollments.index') }}" class="px-5 py-2 text-gray-600 font-medium hover:bg-gray-50 border border-gray-200 rounded-2xl transition text-sm">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-5 rounded-2xl transition shadow-sm flex items-center gap-2 text-sm">
                <i class="fas fa-check"></i>
                <span>Confirm Enrollment</span>
            </button>
        </div>
    </form>
</div>
@endsection
