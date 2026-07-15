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
<div class="flex flex-col lg:flex-row gap-6 lg:h-[calc(100vh-180px)]">



    <!-- ============================================================= -->
    <!-- RIGHT COLUMN: Video Player  +  Description (YouTube-style)     -->
    <!-- ============================================================= -->
    <div class="flex-1 flex flex-col bg-white rounded-2xl overflow-hidden shadow-lg border border-gray-200 relative order-1 lg:order-2 lg:h-full lg:min-h-0">

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

        <!-- Lesson Title + Duration + Description (YouTube-style) -->
        @if($activeLesson)
        <div class="bg-white p-6 border-t border-gray-200 lg:flex-1 lg:min-h-0 lg:overflow-y-auto">
            <div class="flex items-start justify-between gap-4">
                <h2 class="text-2xl font-bold text-gray-900 min-w-0">{{ $activeLesson->title }}</h2>
                @if($activeLesson->duration)
                    @php
                        $durationFormatted = preg_match('/^[0-9]+$/', $activeLesson->duration) ? $activeLesson->duration . ' min' : (preg_match('/^[0-9]+:[0-9]+$/', $activeLesson->duration) ? $activeLesson->duration . ' min' : $activeLesson->duration);
                    @endphp
                    <div class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 text-xs font-semibold">
                        <i class="far fa-clock" style="font-family: 'Font Awesome 6 Free'; font-weight: 400;"></i> {{ $durationFormatted }}
                    </div>
                @endif
            </div>

            @if($activeLesson->description)
                <!-- Description preview with modal trigger -->
                <div id="description-box" class="mt-4 bg-gray-50 rounded-xl p-4 transition">
                    <div id="lesson-description"
                         class="desc-collapsed text-sm text-gray-700 leading-relaxed whitespace-pre-line break-words">{{ $activeLesson->description }}</div>
                    <button type="button" onclick="openDescriptionModal()"
                            class="mt-2 text-sm font-semibold text-indigo-600 hover:text-indigo-700 transition inline-flex items-center gap-1.5">
                        Read Full Description <i class="fas fa-arrow-right text-[10px]"></i>
                    </button>
                </div>
            @endif
             {{-- Lesson Quiz card --}}
            @if($activeLesson->quiz)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden mt-4" id="quiz-section-wrapper">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-indigo-100 bg-gradient-to-r from-indigo-50 to-purple-50">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-purple-50 border border-purple-200 flex items-center justify-center text-purple-600 shrink-0">
                                <i class="fas fa-bolt text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold text-gray-900 leading-tight">Lesson Quiz</h4>
                            </div>
                        </div>
                        <div class="quiz-section-actions">
                            @if($quizAttempt && $quizAttempt->id)
                          <a href="{{ route('student.quizzes.result', [$course->id, $activeLesson->id, $quizAttempt->id]) }}"
   class="btn-view-result inline-flex items-center justify-center gap-2
          px-4 py-2.5 sm:px-5 sm:py-3
          text-white text-sm sm:text-base
          font-semibold rounded-xl
          transition shadow-sm
          w-full sm:w-auto"
   style="background-color: #16a34a !important;">
    <i class="fas fa-eye text-sm sm:text-base"></i>
    <span>View Result</span>
</a>
                            @else
                                <a href="{{ route('student.quizzes.play', [$course->id, $activeLesson->id]) }}"
                                    class="take-quiz-btn inline-flex items-center justify-center gap-2
                                            px-4 py-2.5 sm:px-5 sm:py-3
                                            bg-purple-600 hover:bg-purple-700
                                            text-white text-sm sm:text-base
                                            font-semibold rounded-xl
                                            transition shadow-sm
                                            w-full sm:w-auto">
                                        <i class="fas fa-play text-sm sm:text-base"></i>
                                        <span>Play Quiz</span>
                                    </a>
                            @endif
                        </div>
                    </div>
            </div>
            @endif
        </div>

        
        @endif
    </div>

    <!-- ============================================================= -->
    <!-- LEFT COLUMN: Course Playlist  +  Key Takeaways  +  Lesson Quiz -->
    <!-- ============================================================= -->
    <div class="w-full lg:w-80 xl:w-96 flex flex-col gap-6 order-2 lg:order-1 lg:h-full">

        <!-- Course Playlist (fills the available height on desktop and scrolls) -->
       <div class="flex flex-col bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden h-[320px] order-2 lg:order-1">
            <div class="p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center shrink-0">
                <h3 class="font-bold text-gray-900">Course Content</h3>
                <span class="text-xs font-semibold text-gray-500 bg-white px-2 py-1 rounded border border-gray-200 shadow-sm">{{ $lessons->count() }} Lessons</span>
            </div>

            <div id="lesson-playlist" class="flex-1 min-h-0 overflow-y-auto p-3 space-y-2">
                @forelse($lessons as $lesson)
                    @php
                        $isActive = $activeLesson && $activeLesson->id === $lesson->id;
                        $isUnlocked = in_array($lesson->id, $unlockedLessonIds);

                        // Check if lesson is completed based on quiz status.
                        // NOTE: use a loop-local variable ($lessonAttempted) so we do NOT
                        // clobber the outer $quizAttempt used by the Lesson Quiz card below.
                        $isCompleted = false;
                        if ($lesson->quiz) {
                            $lessonAttempted = \App\Models\QuizAttempt::where('quiz_id', $lesson->quiz->id)
                                ->where('user_id', auth()->id())
                                ->exists();
                            $isCompleted = $lessonAttempted;
                        } else {
                            // No quiz - mark as complete when accessed
                            $isCompleted = true;
                        }
                    @endphp

                    @if($isUnlocked)
                        <a href="{{ route('student.courses.show', [$course->id, $lesson->id]) }}"
                           data-lesson-id="{{ $lesson->id }}"
                           data-index="{{ $loop->index }}"
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
                        @php
                            $lastUnlockedIndex = count($unlockedLessonIds) - 1;
                            $targetLessonId = $unlockedLessonIds[$lastUnlockedIndex] ?? 0;
                            $targetLesson = $lessons->firstWhere('id', $targetLessonId);
                        @endphp
                        <button type="button"
                            onclick="openLockedModal(
                                '{{ addslashes($targetLesson->title ?? 'N/A') }}',
                                '{{ $targetLesson ? route('student.courses.show', [$course->id, $targetLesson->id]) : '#' }}',
                                '{{ addslashes($lesson->title) }}'
                            )"
                            data-index="{{ $loop->index }}"
                            class="block w-full text-left p-3 rounded-xl border border-gray-100 bg-gray-50/50 cursor-pointer opacity-70 hover:opacity-100 hover:bg-gray-100 transition-all duration-200 group"
                            title="Complete the current lesson to unlock this one">
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
                        </button>
                    @endif
                @empty
                    <div class="p-6 text-center text-gray-500">
                        <i class="fas fa-folder-open text-3xl text-gray-300 mb-3"></i>
                        <p class="text-sm font-medium">No lessons have been added to this course yet.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Key Takeaways + Lesson Quiz (pinned below the playlist on desktop) -->
        @if($activeLesson && ($activeLesson->summary || $activeLesson->quiz))
        <div class="space-y-6 order-1 lg:order-2 shrink-0">

            {{-- Key Takeaways (summary) card --}}
            @if($activeLesson->summary)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="flex items-center gap-3 p-4 border-b border-indigo-100 bg-gradient-to-r from-indigo-50 to-purple-50">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 border border-indigo-200 flex items-center justify-center text-indigo-600 shrink-0">
                        <i class="fas fa-list-check text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-sm font-bold text-gray-900 leading-tight">Key Takeaways</h4>
                        <p class="text-[11px] text-gray-500">Quick summary of this lesson</p>
                    </div>
                </div>
            <div class="p-4 break-words overflow-hidden">
                <div class="text-sm text-gray-600 leading-relaxed break-words">
                    {!! \Illuminate\Support\Str::markdown(\Illuminate\Support\Str::limit($activeLesson->summary, 180)) !!}
                </div>
                    <button onclick="openSummaryModal()" class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition">
                        Read Full Summary
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </button>
                </div>
            </div>
            @endif

           

        </div>
        @endif
    </div>



</div>

<!-- Summary Modal -->
@if($activeLesson && $activeLesson->summary)
<div id="summary-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm hidden transition-all duration-300 p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-2xl max-h-[85vh] overflow-hidden flex flex-col">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-5 text-white shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fas fa-list-check"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Key Takeaways</h3>
                        <p class="text-sm text-indigo-200">Full lesson summary</p>
                    </div>
                </div>
                <button onclick="closeSummaryModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body - Scrollable -->
        <div class="p-6 overflow-y-auto flex-1">
            <div class="summary-markdown prose prose-sm max-w-none">
                {!! \Illuminate\Support\Str::markdown($activeLesson->summary) !!}
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 border-t border-gray-100 bg-gray-50 shrink-0 flex justify-end">
            <button onclick="closeSummaryModal()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition shadow-sm">
                <i class="fas fa-check"></i> Got it
            </button>
        </div>
    </div>
</div>
@endif

<!-- Description Modal -->
@if($activeLesson && $activeLesson->description)
<div id="description-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm hidden transition-all duration-300 p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-3xl max-h-[85vh] overflow-hidden flex flex-col">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-gray-700 to-gray-900 p-5 text-white shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fas fa-align-left"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Lesson Description</h3>
                        <p class="text-sm text-gray-300">{{ $activeLesson->title }}</p>
                    </div>
                </div>
                <button onclick="closeDescriptionModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body - Scrollable -->
        <div class="p-6 overflow-y-auto flex-1">
            <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-line break-words">
                {{ $activeLesson->description }}
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 border-t border-gray-100 bg-gray-50 shrink-0 flex justify-end">
            <button onclick="closeDescriptionModal()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white text-sm font-semibold rounded-lg transition shadow-sm">
                <i class="fas fa-check"></i> Got it
            </button>
        </div>
    </div>
</div>
@endif

<!-- Locked Lesson Modal -->
<div id="locked-lesson-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm hidden transition-all duration-300 p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-full max-w-md overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-amber-500 to-orange-600 p-5 text-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                    <i class="fas fa-lock"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-lg">Lesson Locked</h3>
                    <p class="text-sm text-amber-200">Complete the prerequisites first</p>
                </div>
                <button onclick="closeLockedModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition shrink-0">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-full bg-amber-50 border border-amber-200 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lock text-2xl text-amber-500"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2" id="locked-modal-title">Lesson is Locked</h4>
                <p class="text-sm text-gray-600 leading-relaxed" id="locked-modal-description">
                    You need to complete the current lesson before you can access this one.
                </p>
            </div>

            <!-- Prerequisite info box -->
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center shrink-0">
                        <i class="fas fa-play text-xs text-gray-500"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider mb-0.5">Prerequisite Lesson</p>
                        <p class="text-sm font-semibold text-gray-900 truncate" id="locked-modal-prev-lesson">Loading...</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col gap-2">
                <a id="locked-modal-go-btn" href="#"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition shadow-sm">
                    <i class="fas fa-arrow-right"></i> Go to Current Lesson
                </a>
                <button onclick="closeLockedModal()"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-lg transition">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

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

@push('scripts')
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
            let certificateHtml = '';
            if (data.certificate_generated) {
                certificateHtml = `
                    <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center shrink-0">
                                <i class="fas fa-award text-yellow-600"></i>
                            </div>
                            <div class="text-left">
                                <h5 class="text-sm font-bold text-yellow-800">Congratulations!</h5>
                                <p class="text-xs text-yellow-600">You've completed all lessons! Your certificate is ready.</p>
                            </div>
                        </div>
                        <a href="${data.certificate_url}" class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-semibold rounded-lg transition">
                            <i class="fas fa-download"></i> Download Certificate
                        </a>
                    </div>
                `;
            }
            feedback.innerHTML = `
                <div class="p-4 bg-green-50 border border-green-200 rounded-xl text-center">
                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-check-circle text-2xl text-green-600"></i>
                    </div>
                    <h4 class="text-lg font-bold text-green-800 mb-1">Correct!</h4>
                    <p class="text-sm text-green-600 mb-4">Great job! You've unlocked the next lesson.</p>
                    ${certificateHtml}
                    <button onclick="closeQuizModal()" class="mt-3 px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition text-sm">
                        <i class="fas fa-arrow-right"></i> Continue
                    </button>
                </div>
            `;
            // Mark as attempted in the UI with View Result button
            document.querySelector('.take-quiz-btn')?.remove();
            document.querySelector('.quiz-section-actions')?.innerHTML = `
                <a href="{{ route('student.quizzes.result', [$course->id, $activeLesson->id, 'PLACEHOLDER']) }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition shadow-sm w-full">
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
@endpush
@endif


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
                    <div class="text-sm text-gray-700 leading-relaxed chat-markdown">Hi! I'm your AI tutor. Ask me anything about this lesson — I can help clarify concepts, answer questions, or provide additional explanations based on the course material.</div>
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

/* YouTube-style collapsed description (3 lines, then "...more") */
.desc-collapsed {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Chat markdown styles */
.chat-markdown ul {
    list-style: disc;
    padding-left: 1.25rem;
    margin-bottom: 0.5rem;
}
.chat-markdown li {
    margin-bottom: 0.25rem;
    line-height: 1.5;
}
.chat-markdown strong {
    font-weight: 700;
    color: #111827;
}
.chat-markdown em {
    font-style: italic;
}
.chat-markdown br + br {
    display: block;
    content: "";
    margin-top: 0.5rem;
}

/* Markdown rendered content styles */
.summary-markdown h1,
.summary-markdown h2,
.summary-markdown h3,
.summary-markdown h4 {
    @apply font-bold text-gray-900 mt-5 mb-2;
}
.summary-markdown h1 { @apply text-xl; }
.summary-markdown h2 { @apply text-lg; }
.summary-markdown h3 { @apply text-base; }
.summary-markdown h4 { @apply text-sm; }
.summary-markdown p {
    @apply text-gray-700 leading-relaxed mb-3;
}
.summary-markdown ul,
.summary-markdown ol {
    @apply pl-5 mb-3 text-gray-700;
}
.summary-markdown ul { @apply list-disc; }
.summary-markdown ol { @apply list-decimal; }
.summary-markdown li {
    @apply mb-1 leading-relaxed;
}
.summary-markdown strong {
    @apply font-bold text-gray-900;
}
.summary-markdown em {
    @apply italic;
}
.summary-markdown code {
    @apply bg-gray-100 text-sm px-1.5 py-0.5 rounded text-indigo-600 font-mono;
}
.summary-markdown pre {
    @apply bg-gray-900 text-gray-100 rounded-xl p-4 mb-4 overflow-x-auto text-sm font-mono leading-relaxed;
}
.summary-markdown pre code {
    @apply bg-transparent p-0 text-gray-100;
}
.summary-markdown blockquote {
    @apply border-l-4 border-indigo-300 pl-4 py-1 mb-3 text-gray-600 italic bg-indigo-50/50 rounded-r-lg;
}
.summary-markdown hr {
    @apply border-gray-200 my-4;
}
.summary-markdown a {
    @apply text-indigo-600 hover:text-indigo-700 underline;
}
</style>
<script>
// Locked lesson modal functions
function openLockedModal(prevLessonTitle, prevLessonUrl, lessonTitle) {
    document.getElementById('locked-modal-title').textContent = '"' + lessonTitle + '" is Locked';
    document.getElementById('locked-modal-prev-lesson').textContent = prevLessonTitle;
    document.getElementById('locked-modal-go-btn').href = prevLessonUrl;

    const modal = document.getElementById('locked-lesson-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeLockedModal() {
    const modal = document.getElementById('locked-lesson-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Close locked modal on backdrop click
document.addEventListener('click', function(e) {
    const modal = document.getElementById('locked-lesson-modal');
    if (modal && e.target === modal) {
        closeLockedModal();
    }
});

// Summary modal functions
function openSummaryModal() {
    const modal = document.getElementById('summary-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeSummaryModal() {
    const modal = document.getElementById('summary-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Close summary modal on backdrop click
document.addEventListener('click', function(e) {
    const modal = document.getElementById('summary-modal');
    if (modal && e.target === modal) {
        closeSummaryModal();
    }
});

// Description modal functions
function openDescriptionModal() {
    const modal = document.getElementById('description-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeDescriptionModal() {
    const modal = document.getElementById('description-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

// Close description modal on backdrop click
document.addEventListener('click', function(e) {
    const modal = document.getElementById('description-modal');
    if (modal && e.target === modal) {
        closeDescriptionModal();
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
                <div class="text-sm text-gray-700 leading-relaxed chat-markdown">${renderMarkdown(data.reply || 'Sorry, I could not generate a response.')}</div>
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

// Simple markdown renderer for chat messages
function renderMarkdown(text) {
    // First escape HTML
    text = escapeHtml(text);
    // Bold: **text** or __text__
    text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    text = text.replace(/__(.+?)__/g, '<strong>$1</strong>');
    // Italic: *text* or _text_ (but not inside words)
    text = text.replace(/\*(.+?)\*/g, '<em>$1</em>');
    text = text.replace(/_([^_]+)_/g, '<em>$1</em>');
    // Bullet points: • or - or * at start of line
    text = text.replace(/^[\s]*[•\-\*]\s+(.+)$/gm, '<li>$1</li>');
    text = text.replace(/(<li>.*<\/li>(\s|<br\s*\/?>)*)+/g, function(match) {
        return '<ul>' + match.replace(/<br\s*\/?>/g, '') + '</ul>';
    });
    // Newlines to <br>
    text = text.replace(/\n/g, '<br>');
    // Clean up empty wraps
    text = text.replace(/<ul>\s*<\/ul>/g, '');
    return text;
}

// Auto-scroll playlist to show 3 lessons centered around the active one
document.addEventListener('DOMContentLoaded', function() {
    const playlist = document.getElementById('lesson-playlist');
    if (!playlist) return;

    const activeItem = playlist.querySelector('.bg-indigo-50.border-indigo-200');
    if (!activeItem) return;

    const allItems = playlist.querySelectorAll('[data-index]');
    if (allItems.length === 0) return;

    const activeIndex = parseInt(activeItem.getAttribute('data-index'), 10);
    const totalItems = allItems.length;

    // Determine which item should be at the top of the visible area
    let topIndex;
    if (activeIndex === 0) {
        // First lesson: show items 0, 1, 2
        topIndex = 0;
    } else if (activeIndex === totalItems - 1) {
        // Last lesson: show items n-2, n-1, n
        topIndex = Math.max(0, totalItems - 3);
    } else {
        // Middle: show active-1, active, active+1
        topIndex = activeIndex - 1;
    }

    // If topIndex would leave fewer than 3 items visible at the bottom, adjust
    if (topIndex > totalItems - 3) {
        topIndex = Math.max(0, totalItems - 3);
    }

    const topItem = allItems[topIndex];
    if (!topItem) return;

    const containerRect = playlist.getBoundingClientRect();
    const itemRect = topItem.getBoundingClientRect();
    const offset = itemRect.top - containerRect.top - parseInt(getComputedStyle(playlist).paddingTop, 10);

    playlist.scrollTop = playlist.scrollTop + offset;
});
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