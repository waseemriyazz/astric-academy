@extends('layouts.student')

@section('header')
<div class="flex items-center gap-3">
    <a href="{{ route('student.courses.show', [$course->id, $lesson->id]) }}" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:border-indigo-200 transition shadow-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="text-xl font-bold text-gray-900 leading-tight">Quiz Result</h1>
        <p class="text-xs text-gray-500">{{ $lesson->course->title }}</p>
    </div>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
        <!-- Result Header -->
        <div class="bg-gradient-to-r {{ $attempt->is_correct ? 'from-green-600 to-emerald-600' : 'from-red-600 to-pink-600' }} p-6 text-white">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center">
                    <i class="fas {{ $attempt->is_correct ? 'fa-check-circle' : 'fa-times-circle' }} text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold mb-1">{{ $attempt->is_correct ? 'Correct Answer!' : 'Incorrect Answer' }}</h2>
                    <p class="text-sm text-white/80">{{ $lesson->title }}</p>
                </div>
            </div>
        </div>

        <!-- Result Body -->
        <div class="p-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-4 mb-8">
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center text-purple-600">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Your Answer</p>
                            <p class="text-lg font-bold text-gray-900">{{ strtoupper($attempt->selected_answer) }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center text-green-600">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Correct Answer</p>
                            <p class="text-lg font-bold text-gray-900">{{ strtoupper($quiz->correct_answer) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Question Review -->
            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Question Review</h3>
                <div class="p-6 bg-gray-50 rounded-xl border border-gray-200">
                    <p class="text-base text-gray-700 leading-relaxed mb-4">{{ $quiz->question }}</p>
                    
                    <div class="space-y-2">
                        <div class="flex items-start gap-3 p-3 rounded-lg {{ $attempt->selected_answer === 'a' ? ($attempt->is_correct ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200') : 'bg-white border border-gray-200' }}">
                            <span class="w-8 h-8 rounded-lg {{ $attempt->selected_answer === 'a' ? ($attempt->is_correct ? 'bg-green-600 text-white' : 'bg-red-600 text-white') : 'bg-gray-200 text-gray-600' }} font-bold text-sm flex items-center justify-center shrink-0">A</span>
                            <span class="text-sm text-gray-700 flex-1">{{ $quiz->option_a }}</span>
                            @if($attempt->selected_answer === 'a')
                                <i class="fas {{ $attempt->is_correct ? 'fa-check text-green-600' : 'fa-times text-red-600' }}"></i>
                            @endif
                        </div>
                        
                        <div class="flex items-start gap-3 p-3 rounded-lg {{ $attempt->selected_answer === 'b' ? ($attempt->is_correct ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200') : 'bg-white border border-gray-200' }}">
                            <span class="w-8 h-8 rounded-lg {{ $attempt->selected_answer === 'b' ? ($attempt->is_correct ? 'bg-green-600 text-white' : 'bg-red-600 text-white') : 'bg-gray-200 text-gray-600' }} font-bold text-sm flex items-center justify-center shrink-0">B</span>
                            <span class="text-sm text-gray-700 flex-1">{{ $quiz->option_b }}</span>
                            @if($attempt->selected_answer === 'b')
                                <i class="fas {{ $attempt->is_correct ? 'fa-check text-green-600' : 'fa-times text-red-600' }}"></i>
                            @endif
                        </div>
                        
                        <div class="flex items-start gap-3 p-3 rounded-lg {{ $attempt->selected_answer === 'c' ? ($attempt->is_correct ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200') : 'bg-white border border-gray-200' }}">
                            <span class="w-8 h-8 rounded-lg {{ $attempt->selected_answer === 'c' ? ($attempt->is_correct ? 'bg-green-600 text-white' : 'bg-red-600 text-white') : 'bg-gray-200 text-gray-600' }} font-bold text-sm flex items-center justify-center shrink-0">C</span>
                            <span class="text-sm text-gray-700 flex-1">{{ $quiz->option_c }}</span>
                            @if($attempt->selected_answer === 'c')
                                <i class="fas {{ $attempt->is_correct ? 'fa-check text-green-600' : 'fa-times text-red-600' }}"></i>
                            @endif
                        </div>
                        
                        <div class="flex items-start gap-3 p-3 rounded-lg {{ $attempt->selected_answer === 'd' ? ($attempt->is_correct ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200') : 'bg-white border border-gray-200' }}">
                            <span class="w-8 h-8 rounded-lg {{ $attempt->selected_answer === 'd' ? ($attempt->is_correct ? 'bg-green-600 text-white' : 'bg-red-600 text-white') : 'bg-gray-200 text-gray-600' }} font-bold text-sm flex items-center justify-center shrink-0">D</span>
                            <span class="text-sm text-gray-700 flex-1">{{ $quiz->option_d }}</span>
                            @if($attempt->selected_answer === 'd')
                                <i class="fas {{ $attempt->is_correct ? 'fa-check text-green-600' : 'fa-times text-red-600' }}"></i>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-center gap-3">
                <a href="{{ route('student.courses.show', [$course->id, $lesson->id]) }}" class="px-6 py-2.5 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition text-sm">
                    <i class="fas fa-arrow-left"></i> Back to Course
                </a>
            </div>
        </div>
    </div>
</div>
@endsection