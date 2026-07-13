@extends('layouts.student')

@section('header')
<div class="flex items-center gap-3">
    <div>
        <h1 class="text-xl font-bold text-gray-900 leading-tight">My Certificates</h1>
        <p class="text-xs text-gray-500">View and download your course completion certificates</p>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">
    @forelse($certificates as $item)
        @php
            $course = $item['course'];
            $certificate = $item['certificate'];
            $allComplete = $item['all_complete'];
            $totalLessons = $item['total_lessons'];
            $completedLessons = $item['completed_lessons'];
            $progressPercent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
        @endphp

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-300 hover:shadow-md">
            <div class="p-6">
                <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                    <!-- Left: Certificate Preview -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $course->title }}</h3>
                        
                        <!-- Progress Bar -->
                        <div class="mt-3 mb-3">
                            <div class="flex items-center justify-between text-sm mb-1.5">
                                <span class="text-gray-500 font-medium">{{ $completedLessons }}/{{ $totalLessons }} Lessons Completed</span>
                                <span class="text-indigo-600 font-bold">{{ $progressPercent }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500 {{ $allComplete ? 'bg-green-500' : 'bg-indigo-500' }}" style="width: {{ $progressPercent }}%"></div>
                            </div>
                        </div>

                        <!-- Certificate Preview Card (Locked/Unlocked) -->
                        <div class="relative mt-4 rounded-xl overflow-hidden border {{ $allComplete ? 'border-green-200' : 'border-gray-200' }}">
                            <!-- Mini certificate preview -->
                            <div class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 p-5 {{ !$allComplete ? 'opacity-30' : '' }}">
                                <div class="text-center">
                                    <div class="w-12 h-12 mx-auto mb-2 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-full flex items-center justify-center {{ !$allComplete ? 'opacity-40' : '' }}">
                                        <i class="fas fa-certificate text-white text-lg"></i>
                                    </div>
                                    <h4 class="text-sm font-bold {{ !$allComplete ? 'text-gray-300' : 'text-gray-800' }}">Certificate of Completion</h4>
                                    <p class="text-xs mt-1 {{ !$allComplete ? 'text-gray-300' : 'text-gray-500' }}">{{ $course->title }}</p>
                                    @if($certificate)
                                        <p class="text-[10px] text-gray-400 mt-1 font-mono">{{ $certificate->serial_number }}</p>
                                    @endif
                                </div>
                            </div>

                            @if(!$allComplete)
                                <!-- Locked overlay -->
                                <div class="absolute inset-0 flex items-center justify-center z-10">
                                    <div class="text-center">
                                        <div class="w-14 h-14 rounded-full bg-white/80 border border-gray-200 shadow-sm flex items-center justify-center mx-auto mb-3">
                                            <i class="fas fa-lock text-xl text-gray-400"></i>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-500 bg-white/80 px-4 py-1.5 rounded-lg shadow-sm inline-block">Complete all lessons to unlock</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Right: Actions -->
                    <div class="shrink-0 flex flex-col gap-2">
                        @if($allComplete && $certificate)
                            <a href="{{ route('student.certificates.download', $course->id) }}" 
                               class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                                <i class="fas fa-download"></i> Download PDF
                            </a>
                            <a href="{{ route('student.certificates.show', $course->id) }}" 
                               class="inline-flex items-center justify-center gap-2 px-5 py-3 border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-xl transition">
                                <i class="fas fa-eye"></i> View Certificate
                            </a>
                        @elseif($allComplete && !$certificate)
                            <form action="{{ route('student.certificates.generate', $course->id) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition shadow-sm w-full">
                                    <i class="fas fa-award"></i> Claim Certificate
                                </button>
                            </form>
                        @else
                            <a href="{{ route('student.courses.show', $course->id) }}" 
                               class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition">
                                <i class="fas fa-arrow-right"></i> Continue Course
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-certificate text-3xl text-gray-300"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">No Certificates Yet</h3>
            <p class="text-sm text-gray-500 max-w-md mx-auto mb-6">
                You haven't been enrolled in any courses yet. Once you complete a course, your certificate will appear here.
            </p>
            <a href="{{ route('student.dashboard') }}" 
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition shadow-sm">
                <i class="fas fa-arrow-left"></i> Go to Dashboard
            </a>
        </div>
    @endforelse
</div>

@if(count($certificates) > 0)
    <!-- Verification Info -->
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                <i class="fas fa-shield-alt text-blue-600 text-xl"></i>
            </div>
            <div>
                <h4 class="text-sm font-bold text-gray-900 mb-1">Verified Certificates</h4>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Each certificate has a unique serial number ({{ 'AST-' . date('Y') . '-XXXX' }}) that can be used to verify authenticity. 
                    Employers or third parties can verify your certificates by contacting our support team with the serial number.
                </p>
            </div>
        </div>
    </div>
@endif
@endsection