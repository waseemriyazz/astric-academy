@extends('layouts.student')

@section('header')
<div class="flex items-center gap-3">
    <a href="{{ route('student.dashboard') }}" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:border-indigo-200 transition shadow-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="text-xl font-bold text-gray-900 leading-tight">{{ $course->title }}</h1>
        <p class="text-xs text-gray-500">{{ $lessons->count() }} Lessons</p>
    </div>
</div>
@endsection

@section('content')
<div class="flex flex-col lg:flex-row gap-6 h-[calc(100vh-180px)]">
    
    <!-- Left Column: Video Player & Quiz Area -->
    <div class="flex-1 flex flex-col bg-white rounded-2xl overflow-hidden shadow-lg border border-gray-200 relative">
        <!-- Video Player -->
        <div class="bg-black">
            @if($activeLesson && $activeLesson->youtube_url)
                @php
                    $youtubeId = '';
                    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $activeLesson->youtube_url, $match)) {
                        $youtubeId = $match[1];
                    }
                @endphp
                
                @if($youtubeId)
                    <div class="w-full relative" style="padding-bottom: 56.25%;">
                        <iframe 
                            id="video-player"
                            class="absolute top-0 left-0 w-full h-full"
                            src="https://www.youtube.com/embed/{{ $youtubeId }}?rel=0&modestbranding=1&showinfo=0&enablejsapi=1&origin={{ url()->current() }}" 
                            title="YouTube video player" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                            allowfullscreen>
                        </iframe>
                    </div>
                @else
                    <div class="flex items-center justify-center text-center p-8 bg-gray-900 text-white" style="padding-bottom: 56.25%;">
                        <div>
                            <i class="fab fa-youtube text-5xl text-gray-600 mb-4"></i>
                            <h3 class="text-xl font-bold mb-2">Invalid Video URL</h3>
                            <p class="text-gray-400 max-w-md">The YouTube video URL provided for this lesson appears to be invalid.</p>
                        </div>
                    </div>
                @endif
                
            @elseif($activeLesson && $activeLesson->vimeo_url)
                @php
                    $vimeoId = '';
                    if (preg_match('/(?:www\.|player\.)?vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)/i', $activeLesson->vimeo_url, $match)) {
                        $vimeoId = $match[1];
                    }
                @endphp
                
                @if($vimeoId)
                    <div class="w-full relative" style="padding-bottom: 56.25%;">
                        <iframe 
                            id="video-player"
                            class="absolute top-0 left-0 w-full h-full"
                            src="https://player.vimeo.com/video/{{ $vimeoId }}?title=0&byline=0&portrait=0&badge=0" 
                            title="Vimeo video player" 
                            frameborder="0" 
                            allow="autoplay; fullscreen; picture-in-picture" 
                            allowfullscreen>
                        </iframe>
                    </div>
                @else
                    <div class="flex items-center justify-center text-center p-8 bg-gray-900 text-white" style="padding-bottom: 56.25%;">
                        <div>
                            <i class="fab fa-vimeo text-5xl text-gray-600 mb-4"></i>
                            <h3 class="text-xl font-bold mb-2">Invalid Video URL</h3>
                            <p class="text-gray-400 max-w-md">The Vimeo video URL provided for this lesson appears to be invalid.</p>
                        </div>
                    </div>
                @endif
                
            @else
                <div class="flex items-center justify-center text-center p-8 bg-gray-900 text-white" style="padding-bottom: 56.25%;">
                    <div>
                        <div class="w-20 h-20 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-film text-3xl text-gray-600"></i>
                        </div>
                        <h3 class="text-2xl font-bold mb-2">No lesson selected or available</h3>
                        <p class="text-gray-400 max-w-md">There are no lessons available to watch right now.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Lesson Info Bar -->
        @if($activeLesson)
        <div class="bg-white p-6 border-t border-gray-200 overflow-y-auto max-h-[calc(100vh-400px)]">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $activeLesson->title }}</h2>
                    <div class="relative">
                        <p class="text-sm text-gray-600 leading-relaxed line-clamp-3" id="description-text">{{ $activeLesson->description ?? 'No description provided for this lesson.' }}</p>
                        <button id="show-more-desc-btn" onclick="openDescriptionModal()" class="mt-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition inline-flex items-center gap-1">
                            Show more <i class="fas fa-chevron-right text-[10px]" style="font-weight: 900;"></i>
                        </button>
                    </div>
                    
                    @if($activeLesson->summary)
                        <div class="mt-4">
                            <button onclick="openSummaryModal()" class="mt-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition inline-flex items-center gap-1">
                                <i class="fas fa-list-check" style="font-weight: 900;"></i> Show Summary
                            </button>
                        </div>
                    @endif
                </div>
                @if($activeLesson->duration)
                    @php
                        $durationFormatted = preg_match('/^[0-9]+$/', $activeLesson->duration) ? $activeLesson->duration . ' min' : (preg_match('/^[0-9]+:[0-9]+$/', $activeLesson->duration) ? $activeLesson->duration . ' min' : $activeLesson->duration);
                    @endphp
                    <div class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 text-xs font-semibold">
                        <i class="far fa-clock" style="font-family: 'Font Awesome 6 Free'; font-weight: 400;"></i> {{ $durationFormatted }}
                    </div>
                @endif
            </div>

            <!-- Quiz Section -->
            @if($activeLesson->quiz)
                <div class="mt-6 pt-6 border-t border-gray-200" id="quiz-section-wrapper">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-purple-50 border border-purple-200 flex items-center justify-center text-purple-600">
                                <i class="fas fa-question-circle text-sm"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-900">Lesson Quiz</h4>
                                <p class="text-xs text-gray-500">Attempt this quiz to unlock the next lesson</p>
                            </div>
                        </div>
                        <div class="quiz-section-actions flex items-center gap-2 flex-wrap">
                            @if($quizAttempt && $quizAttempt->id)
                                <a href="{{ route('student.quizzes.result', [$course->id, $activeLesson->id, $quizAttempt->id]) }}" 
                                   class="btn-view-result inline-flex items-center justify-center gap-2 px-4 py-2.5 text-white text-sm font-semibold rounded-lg transition shadow-sm w-full sm:w-auto"
                                   style="background-color: #16a34a !important;">
                                    <i class="fas fa-eye"></i> View Result
                                </a>
                            @else
                                <a href="{{ route('student.quizzes.play', [$course->id, $activeLesson->id]) }}" 
                                   class="take-quiz-btn inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg transition shadow-sm w-full sm:w-auto">
                                    <i class="fas fa-play"></i> Play Quiz
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
        @endif
    </div>

    <!-- Right Column: Course Playlist -->
    <div class="w-full lg:w-80 xl:w-96 flex flex-col bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden h-full">
        <div class="p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center shrink-0">
            <h3 class="font-bold text-gray-900">Course Content</h3>
            <span class="text-xs font-semibold text-gray-500 bg-white px-2 py-1 rounded border border-gray-200 shadow-sm">{{ $lessons->count() }} Lessons</span>
        </div>
        
        <div class="flex-1 overflow-y-auto p-3 space-y-2">
            @forelse($lessons as $lesson)
                @php
                    $isActive = $activeLesson && $activeLesson->id === $lesson->id;
                    $isUnlocked = in_array($lesson->id, $unlockedLessonIds);
                    
                    // Check if lesson is completed based on quiz status
                    $isCompleted = false;
                    if ($lesson->quiz) {
                        // Has quiz - check if attempted (regardless of correct/wrong)
                        $quizAttempt = \App\Models\QuizAttempt::where('quiz_id', $lesson->quiz->id)
                            ->where('user_id', auth()->id())
                            ->exists();
                        $isCompleted = $quizAttempt;
                    } else {
                        // No quiz - mark as complete when accessed
                        $isCompleted = true;
                    }
                @endphp
                
                @if($isUnlocked)
                    <a href="{{ route('student.courses.show', [$course->id, $lesson->id]) }}" 
                       data-lesson-id="{{ $lesson->id }}"
                       class="block p-3 rounded-xl border transition-all duration-200 group relative overflow-hidden {{ $isActive ? 'bg-indigo-50 border-indigo-200 shadow-[0_2px_8px_rgb(79,70,229,0.1)]' : 'bg-white border-transparent hover:bg-gray-50 hover:border-gray-200' }}">
                       
                       @if($isActive)
                           <div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-600"></div>
                       @endif
                        
                        <div class="flex items-start gap-3 pl-1">
                            <div class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center {{ $isCompleted ? 'bg-green-100 text-green-600' : ($isActive ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-400 group-hover:bg-indigo-50 group-hover:text-indigo-500') }} transition-colors">
                                @if($isCompleted)
                                    <i class="fas fa-check text-xs"></i>
                                @elseif($isActive)
                                    <i class="fas fa-play text-[10px] ml-0.5"></i>
                                @else
                                    <span class="text-xs font-bold">{{ $loop->iteration }}</span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-semibold truncate {{ $isActive ? 'text-indigo-900' : 'text-gray-900 group-hover:text-indigo-700' }} transition-colors">
                                    {{ $lesson->title }}
                                </h4>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] uppercase font-bold tracking-wider {{ $isActive ? 'text-indigo-500' : 'text-gray-400' }}">
                                        @if($lesson->youtube_url || $lesson->vimeo_url) Video @else Text @endif
                                    </span>
                                    @if($lesson->duration)
                                        @php
                                            $dur = preg_match('/^[0-9]+$/', $lesson->duration) ? $lesson->duration . ' min' : (preg_match('/^[0-9]+:[0-9]+$/', $lesson->duration) ? $lesson->duration . ' min' : $lesson->duration);
                                        @endphp
                                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                        <span class="text-[11px] text-gray-500 flex items-center gap-1">
                                            <i class="far fa-clock" style="font-family: 'Font Awesome 6 Free'; font-weight: 400;"></i> {{ $dur }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @else
                    <div class="block p-3 rounded-xl border border-gray-100 bg-gray-50/50 cursor-not-allowed opacity-70" title="Complete the previous lesson's quiz to unlock this lesson">
                        <div class="flex items-start gap-3 pl-1">
                            <div class="shrink-0 w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-400">
                                <i class="fas fa-lock text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-semibold text-gray-400 truncate">
                                    {{ $lesson->title }}
                                </h4>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400">
                                        Locked
                                    </span>
                                    @if($lesson->duration)
                                        @php
                                            $dur = preg_match('/^[0-9]+$/', $lesson->duration) ? $lesson->duration . ' min' : (preg_match('/^[0-9]+:[0-9]+$/', $lesson->duration) ? $lesson->duration . ' min' : $lesson->duration);
                                        @endphp
                                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                        <span class="text-[11px] text-gray-400 flex items-center gap-1">
                                            <i class="far fa-clock" style="font-family: 'Font Awesome 6 Free'; font-weight: 400;"></i> {{ $dur }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="p-6 text-center text-gray-500">
                    <i class="fas fa-folder-open text-3xl text-gray-300 mb-3"></i>
                    <p class="text-sm font-medium">No lessons have been added to this course yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('modals')
<!-- Description Modal -->
<div id="description-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm hidden transition-all duration-300 p-4" onclick="if(event.target===this)closeDescriptionModal()">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 p-5 text-white shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fas fa-align-left"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">{{ $activeLesson->title ?? 'Lesson' }}</h3>
                        <p class="text-sm text-indigo-200">Full Description</p>
                    </div>
                </div>
                <button onclick="closeDescriptionModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto flex-1">
            <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed whitespace-pre-line">
                {{ $activeLesson->description ?? 'No description provided for this lesson.' }}
            </div>
        </div>
    </div>
</div>

<!-- Summary Modal -->
@if($activeLesson && $activeLesson->summary)
<div id="summary-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm hidden transition-all duration-300 p-4" onclick="if(event.target===this)closeSummaryModal()">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 p-5 text-white shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fas fa-list-check"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Lesson Summary</h3>
                        <p class="text-sm text-indigo-200">Key points & recap</p>
                    </div>
                </div>
                <button onclick="closeSummaryModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto flex-1">
            <div class="bg-amber-50 border border-amber-100 rounded-xl p-5">
                <div class="flex items-start gap-3 mb-3">
                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white shrink-0 mt-0.5">
                        <i class="fas fa-lightbulb text-sm"></i>
                    </span>
                    <p class="text-sm text-amber-900 font-medium">Summary</p>
                </div>
                <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed whitespace-pre-line pl-11">
                    {{ $activeLesson->summary }}
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Quiz Modal -->
@if($activeLesson && $activeLesson->quiz)
<div id="quiz-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm hidden transition-all duration-300 p-4" onclick="if(event.target===this)closeQuizModal()">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 p-5 text-white shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Lesson Quiz</h3>
                        <p class="text-sm text-purple-200">Test your knowledge</p>
                    </div>
                </div>
                <button onclick="closeQuizModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body - Scrollable -->
        <div class="p-6 overflow-y-auto flex-1">
            <p class="text-lg font-semibold text-gray-900 mb-2">{{ $activeLesson->quiz->question }}</p>
            <p class="text-sm text-gray-500 mb-6">Select the correct answer below</p>

            <div id="quiz-options" class="space-y-3">
                <button onclick="submitQuizAnswer('a')" class="quiz-option w-full text-left p-4 rounded-xl border-2 border-gray-200 hover:border-purple-500 hover:bg-purple-50 cursor-pointer transition-all duration-200 flex items-center gap-4 group" data-option="a">
                    <span class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 font-bold text-sm flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-all duration-200 shrink-0">A</span>
                    <span class="text-sm text-gray-700 font-medium group-hover:text-gray-900 transition flex-1">{{ $activeLesson->quiz->option_a }}</span>
                    <i class="fas fa-mouse-pointer text-gray-300 group-hover:text-purple-600 transition shrink-0"></i>
                </button>
                <button onclick="submitQuizAnswer('b')" class="quiz-option w-full text-left p-4 rounded-xl border-2 border-gray-200 hover:border-purple-500 hover:bg-purple-50 cursor-pointer transition-all duration-200 flex items-center gap-4 group" data-option="b">
                    <span class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 font-bold text-sm flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-all duration-200 shrink-0">B</span>
                    <span class="text-sm text-gray-700 font-medium group-hover:text-gray-900 transition flex-1">{{ $activeLesson->quiz->option_b }}</span>
                    <i class="fas fa-mouse-pointer text-gray-300 group-hover:text-purple-600 transition shrink-0"></i>
                </button>
                <button onclick="submitQuizAnswer('c')" class="quiz-option w-full text-left p-4 rounded-xl border-2 border-gray-200 hover:border-purple-500 hover:bg-purple-50 cursor-pointer transition-all duration-200 flex items-center gap-4 group" data-option="c">
                    <span class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 font-bold text-sm flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-all duration-200 shrink-0">C</span>
                    <span class="text-sm text-gray-700 font-medium group-hover:text-gray-900 transition flex-1">{{ $activeLesson->quiz->option_c }}</span>
                    <i class="fas fa-mouse-pointer text-gray-300 group-hover:text-purple-600 transition shrink-0"></i>
                </button>
                <button onclick="submitQuizAnswer('d')" class="quiz-option w-full text-left p-4 rounded-xl border-2 border-gray-200 hover:border-purple-500 hover:bg-purple-50 cursor-pointer transition-all duration-200 flex items-center gap-4 group" data-option="d">
                    <span class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 font-bold text-sm flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-all duration-200 shrink-0">D</span>
                    <span class="text-sm text-gray-700 font-medium group-hover:text-gray-900 transition flex-1">{{ $activeLesson->quiz->option_d }}</span>
                    <i class="fas fa-mouse-pointer text-gray-300 group-hover:text-purple-600 transition shrink-0"></i>
                </button>
            </div>

            <!-- Feedback Area -->
            <div id="quiz-feedback" class="mt-6 hidden"></div>

            <div id="quiz-loading" class="mt-6 hidden">
                <div class="flex items-center justify-center gap-2 py-4">
                    <div class="w-2 h-2 rounded-full bg-purple-400 animate-bounce" style="animation-delay: 0s"></div>
                    <div class="w-2 h-2 rounded-full bg-purple-500 animate-bounce" style="animation-delay: 0.15s"></div>
                    <div class="w-2 h-2 rounded-full bg-purple-600 animate-bounce" style="animation-delay: 0.3s"></div>
                </div>
            </div>
        </div>
    </div>
</div>

        <script>
        let quizSubmitting = false;

        function openQuizModal() {
    const modal = document.getElementById('quiz-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.getElementById('quiz-feedback').classList.add('hidden');
    // Reset option styles
    document.querySelectorAll('.quiz-option').forEach(el => {
        el.classList.remove('border-green-500', 'bg-green-50', 'border-red-500', 'bg-red-50', 'pointer-events-none');
        el.classList.add('border-gray-200', 'hover:border-purple-500', 'hover:bg-purple-50', 'cursor-pointer');
    });
    quizSubmitting = false;
}

function closeQuizModal() {
    document.getElementById('quiz-modal').classList.add('hidden');
    document.getElementById('quiz-modal').classList.remove('flex');
}

function submitQuizAnswer(answer) {
    if (quizSubmitting) return;
    quizSubmitting = true;

    // Disable all options
    document.querySelectorAll('.quiz-option').forEach(el => {
        el.classList.add('pointer-events-none', 'cursor-not-allowed');
        el.classList.remove('hover:border-purple-500', 'hover:bg-purple-50', 'cursor-pointer');
    });

    // Highlight selected with animation
    const selectedOption = document.querySelector(`.quiz-option[data-option="${answer}"]`);
    selectedOption.classList.remove('border-purple-500', 'bg-purple-50');
    selectedOption.classList.add('border-purple-600', 'bg-purple-100', 'scale-[1.02]', 'shadow-md');

    // Show loading
    document.getElementById('quiz-loading').classList.remove('hidden');

    fetch('{{ route("student.courses.quiz.attempt", [$course->id, $activeLesson->id]) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ selected_answer: answer })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('quiz-loading').classList.add('hidden');
        
        const feedback = document.getElementById('quiz-feedback');
        feedback.classList.remove('hidden');

        if (data.is_correct) {
            feedback.innerHTML = `
                <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-center">
                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-check-circle text-2xl text-green-600"></i>
                    </div>
                    <h4 class="text-lg font-bold text-green-800 mb-1">Great job!</h4>
                    <p class="text-sm text-green-600 mb-4">You've unlocked the next lesson.</p>
                    <button onclick="closeQuizModal()" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition text-sm">
                        <i class="fas fa-arrow-right"></i> Continue
                    </button>
                </div>
            `;
            // Mark as attempted in the UI with View Result button
            document.querySelector('.take-quiz-btn')?.remove();
            document.querySelector('.quiz-section-actions')?.innerHTML = `
                <a href="{{ route('student.quizzes.result', [$course->id, $activeLesson->id, 'PLACEHOLDER']) }}" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition shadow-sm w-full sm:w-auto">
                   <i class="fas fa-eye"></i> View Result
                </a>
            `;
            // Reload page after a brief delay to refresh unlocked state
            setTimeout(() => { window.location.reload(); }, 2000);
        } else {
            const correctLetter = data.correct_answer.toUpperCase();
            // Get the text content from the option (excluding the icon)
            const correctOptionElement = document.querySelector(`.quiz-option[data-option="${data.correct_answer}"]`);
            const correctText = correctOptionElement.querySelector('span.flex-1').textContent;
            
            // Highlight correct answer
            correctOptionElement.classList.add('border-green-500', 'bg-green-50');
            document.querySelector(`.quiz-option[data-option="${answer}"]`).classList.add('border-red-500', 'bg-red-50');

            feedback.innerHTML = `
                <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-center">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-times-circle text-2xl text-red-600"></i>
                    </div>
                    <h4 class="text-lg font-bold text-red-800 mb-1">Better luck next time!</h4>
                    <p class="text-sm text-red-600 mb-2">The correct answer was: <strong>${correctLetter}. ${correctText}</strong></p>
                    <p class="text-xs text-red-500 mb-4">You've unlocked the next lesson. Keep learning!</p>
                    <button onclick="closeQuizModal()" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition text-sm">
                        <i class="fas fa-arrow-right"></i> Continue
                    </button>
                </div>
            `;
            quizSubmitting = false;
        }
    })
    .catch(error => {
        document.getElementById('quiz-loading').classList.add('hidden');
        document.getElementById('quiz-feedback').classList.remove('hidden');
        document.getElementById('quiz-feedback').innerHTML = `
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-center">
                <p class="text-sm text-red-600">Something went wrong. Please try again.</p>
                <button onclick="openQuizModal()" class="mt-3 px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition text-sm">
                    Try Again
                </button>
            </div>
        `;
        quizSubmitting = false;
    });
}
        </script>
    @endif
@endpush


<!-- AI Chat Bot - Floating Button & Slide-Out Panel -->
<div id="ai-chat" 
     data-course-id="{{ $course->id }}" 
     data-lesson-id="{{ $activeLesson->id ?? '' }}"
     data-chat-url="{{ route('student.courses.chat', [$course->id, $activeLesson->id ?? 0]) }}">
    
    <!-- Floating Chat Button -->
    <button id="chat-toggle-btn" 
            class="fixed bottom-6 right-6 z-50 w-14 h-14 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300 flex items-center justify-center"
            onclick="toggleChat()">
        <i id="chat-icon" class="fas fa-comment-dots text-xl"></i>
    </button>

    <!-- Chat Panel -->
    <div id="chat-panel" 
         class="fixed bottom-24 right-6 z-50 w-96 h-[500px] bg-white rounded-2xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden transition-all duration-300 opacity-0 invisible scale-95 origin-bottom-right">
        
        <!-- Chat Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-4 text-white shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fas fa-robot text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm">AI Tutor</h3>
                        <p class="text-sm font-medium text-white/90">Ask anything about this video</p>
                    </div>
                </div>
                <button onclick="toggleChat()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>
        
        <!-- Chat Messages -->
        <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fas fa-robot text-xs text-indigo-600"></i>
                </div>
                <div class="bg-white rounded-2xl rounded-tl-sm p-3 shadow-sm border border-gray-100 max-w-[85%]">
                    <p class="text-sm text-gray-700 leading-relaxed">Hi! I'm your AI tutor. Ask me anything about this lesson — I can help clarify concepts, answer questions, or provide additional explanations based on the course material.</p>
                </div>
            </div>
        </div>
        
        <!-- Chat Input -->
        <div class="p-4 border-t border-gray-100 bg-white shrink-0">
            <form id="chat-form" onsubmit="sendMessage(event)" class="flex items-center gap-2">
                <input type="text" 
                       id="chat-input" 
                       placeholder="Ask a question..." 
                       class="flex-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                       maxlength="2000"
                       autocomplete="off">
                <button type="submit" 
                        id="chat-send-btn"
                        class="w-10 h-10 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed flex items-center justify-center transition shadow-sm">
                    <i class="fas fa-paper-plane text-sm"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.btn-view-result:hover {
    background-color: #15803d !important;
}
</style>
<script>
// Description & Summary Modal Functions
function openDescriptionModal() {
    const modal = document.getElementById('description-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeDescriptionModal() {
    const modal = document.getElementById('description-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

function openSummaryModal() {
    const modal = document.getElementById('summary-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
}

function closeSummaryModal() {
    const modal = document.getElementById('summary-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }
}

// Hide "Show more" button if description is short (fits within 3 lines)
document.addEventListener('DOMContentLoaded', function() {
    const descText = document.getElementById('description-text');
    const showMoreBtn = document.getElementById('show-more-desc-btn');
    if (descText && showMoreBtn) {
        // Check if content overflows the 3-line clamp
        if (descText.scrollHeight <= descText.clientHeight) {
            showMoreBtn.classList.add('hidden');
        }
    }
});

let chatOpen = false;

function toggleChat() {
    const panel = document.getElementById('chat-panel');
    const icon = document.getElementById('chat-icon');
    chatOpen = !chatOpen;
    
    if (chatOpen) {
        panel.classList.remove('opacity-0', 'invisible', 'scale-95');
        panel.classList.add('opacity-100', 'visible', 'scale-100');
        icon.classList.remove('fa-comment-dots');
        icon.classList.add('fa-times');
        document.getElementById('chat-input').focus();
    } else {
        panel.classList.remove('opacity-100', 'visible', 'scale-100');
        panel.classList.add('opacity-0', 'invisible', 'scale-95');
        icon.classList.remove('fa-times');
        icon.classList.add('fa-comment-dots');
    }
}

async function sendMessage(event) {
    event.preventDefault();
    
    const input = document.getElementById('chat-input');
    const sendBtn = document.getElementById('chat-send-btn');
    const messages = document.getElementById('chat-messages');
    const message = input.value.trim();
    
    if (!message) return;
    
    input.disabled = true;
    sendBtn.disabled = true;
    
    const userDiv = document.createElement('div');
    userDiv.className = 'flex items-start gap-3 justify-end';
    userDiv.innerHTML = `
        <div class="bg-indigo-600 rounded-2xl rounded-tr-sm p-3 shadow-sm max-w-[85%]">
            <p class="text-sm text-white leading-relaxed">${escapeHtml(message)}</p>
        </div>
        <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center shrink-0 mt-0.5">
            <i class="fas fa-user text-xs text-white"></i>
        </div>
    `;
    messages.appendChild(userDiv);
    messages.scrollTop = messages.scrollHeight;
    
    input.value = '';
    
    const loadingDiv = document.createElement('div');
    loadingDiv.id = 'chat-loading';
    loadingDiv.className = 'flex items-start gap-3';
    loadingDiv.innerHTML = `
        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center shrink-0 mt-0.5">
            <i class="fas fa-robot text-xs text-indigo-600"></i>
        </div>
        <div class="bg-white rounded-2xl rounded-tl-sm p-4 shadow-sm border border-gray-100">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-indigo-400 animate-bounce" style="animation-delay: 0s"></div>
                <div class="w-2 h-2 rounded-full bg-indigo-500 animate-bounce" style="animation-delay: 0.15s"></div>
                <div class="w-2 h-2 rounded-full bg-indigo-600 animate-bounce" style="animation-delay: 0.3s"></div>
            </div>
        </div>
    `;
    messages.appendChild(loadingDiv);
    messages.scrollTop = messages.scrollHeight;
    
    try {
        const chatContainer = document.getElementById('ai-chat');
        const response = await fetch(chatContainer.dataset.chatUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ message })
        });
        
        document.getElementById('chat-loading')?.remove();
        
        const data = await response.json();
        
        const botDiv = document.createElement('div');
        botDiv.className = 'flex items-start gap-3';
        botDiv.innerHTML = `
            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center shrink-0 mt-0.5">
                <i class="fas fa-robot text-xs text-indigo-600"></i>
            </div>
            <div class="bg-white rounded-2xl rounded-tl-sm p-3 shadow-sm border border-gray-100 max-w-[85%]">
                <p class="text-sm text-gray-700 leading-relaxed">${escapeHtml(data.reply || 'Sorry, I could not generate a response.')}</p>
            </div>
        `;
        messages.appendChild(botDiv);
        messages.scrollTop = messages.scrollHeight;
        
    } catch (error) {
        document.getElementById('chat-loading')?.remove();
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'flex items-start gap-3';
        errorDiv.innerHTML = `
            <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center shrink-0 mt-0.5">
                <i class="fas fa-exclamation-triangle text-xs text-red-600"></i>
            </div>
            <div class="bg-red-50 rounded-2xl rounded-tl-sm p-3 shadow-sm border border-red-100 max-w-[85%]">
                <p class="text-sm text-red-700 leading-relaxed">Sorry, something went wrong. Please try again.</p>
            </div>
        `;
        messages.appendChild(errorDiv);
        messages.scrollTop = messages.scrollHeight;
    }
    
    input.disabled = false;
    sendBtn.disabled = false;
    input.focus();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<!-- Footer Support Contact -->
<div class="mt-6 pt-4 border-t border-gray-200">
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4 shadow-sm">
        <div class="flex items-center justify-center gap-3">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                <i class="fas fa-headset text-blue-600"></i>
            </div>
            <div class="flex-1 min-w-0 text-center">
                <h4 class="text-sm font-bold text-gray-900 mb-1">Need Help?</h4>
                <p class="text-xs text-gray-600 mb-2">Experiencing issues with the course?</p>
                <a href="mailto:support@astryxacademy.com" 
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition">
                    <i class="fas fa-envelope"></i>
                    Contact Support
                </a>
            </div>
        </div>
    </div>
</div>


@endsection