@extends('layouts.student')

@section('header', 'My Profile')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    
    <!-- Profile Information Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-8">
            <div class="flex items-center gap-6">
                <div class="w-20 h-20 rounded-full bg-white/20 backdrop-blur flex items-center justify-center text-white text-3xl font-bold border-2 border-white/30">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div class="text-white">
                    <h3 class="text-2xl font-bold mb-1">{{ $user->name }}</h3>
                    <p class="text-indigo-100 flex items-center gap-2">
                        <i class="fas fa-envelope"></i>
                        {{ $user->email }}
                    </p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <h4 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-info-circle text-indigo-600"></i>
                Account Information
            </h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Full Name</p>
                    <p class="text-gray-900 font-medium">{{ $user->name }}</p>
                </div>
                
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Email Address</p>
                    <p class="text-gray-900 font-medium">{{ $user->email }}</p>
                </div>
                
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Account Type</p>
                    <p class="text-gray-900 font-medium capitalize">{{ $user->role ?? 'Student' }}</p>
                </div>
                
            </div>
            
            <div class="mt-6 pt-6 border-t border-gray-200">
                <button onclick="document.getElementById('edit-profile-section').scrollIntoView({behavior: 'smooth'})" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition shadow-sm">
                    <i class="fas fa-edit"></i>
                    Edit Profile
                </button>
            </div>
        </div>
    </div>
    
    <!-- My Courses Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
            <h4 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-book-open text-indigo-600"></i>
                My Courses
                <span class="ml-2 px-2.5 py-0.5 bg-indigo-100 text-indigo-700 text-xs font-bold rounded-full">
                    {{ $courses->count() }}
                </span>
            </h4>
        </div>
        
        <div class="p-6">
            @if($courses->isEmpty())
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-book-open text-3xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 mb-2">You haven't enrolled in any courses yet.</p>
                    <p class="text-sm text-gray-400">Courses will appear here once an administrator grants you access.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($courses as $course)
                        <div class="border border-gray-200 rounded-xl p-5 hover:border-indigo-300 hover:shadow-md transition-all duration-200">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <h5 class="text-base font-bold text-gray-900 mb-2">{{ $course->title }}</h5>
                                    <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $course->description ?? 'No description available' }}</p>
                                    
                                    <div class="flex flex-wrap items-center gap-3 text-xs">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-700 font-semibold border border-indigo-100">
                                            <i class="fas fa-list-ul"></i>
                                            {{ $course->lessons->count() }} Lessons
                                        </span>
                                        
                                        @if($course->tools_count > 0)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-purple-50 text-purple-700 font-semibold border border-purple-100">
                                                <i class="fas fa-tools"></i>
                                                {{ $course->tools_count }} Tools
                                            </span>
                                        @endif
                                        
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-green-50 text-green-700 font-semibold border border-green-100">
                                            <i class="fas fa-check-circle"></i>
                                            Active
                                        </span>
                                    </div>
                                </div>
                                
                                <a href="{{ route('student.courses.show', $course->id) }}" class="shrink-0 inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition shadow-sm text-sm">
                                    <i class="fas fa-play-circle"></i>
                                    Continue
                                </a>
                            </div>
                            
                            @if($course->lessons->count() > 0)
                                <div class="mt-4 pt-4 border-t border-gray-100">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Recent Lessons</p>
                                    <div class="space-y-1.5">
                                        @foreach($course->lessons->take(3) as $lesson)
                                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                                <i class="fas fa-circle text-[8px] text-indigo-400"></i>
                                                <span>{{ $lesson->title }}</span>
                                            </div>
                                        @endforeach
                                        @if($course->lessons->count() > 3)
                                            <p class="text-xs text-gray-400 mt-1">+{{ $course->lessons->count() - 3 }} more lessons</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    
    <!-- Edit Profile Section -->
    <div id="edit-profile-section" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
            <h4 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-edit text-indigo-600"></i>
                Edit Profile
            </h4>
        </div>
        
        <div class="p-6">
            @include('profile.partials.edit-form')
        </div>
    </div>
    
</div>
@endsection