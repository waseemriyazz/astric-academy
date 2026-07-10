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
    
    <!-- Left Column: Video Player -->
    <div class="flex-1 flex flex-col h-full bg-black rounded-2xl overflow-hidden shadow-lg border border-gray-200 relative">
        @if($activeLesson && $activeLesson->youtube_url)
            @php
                // Extract YouTube Video ID
                $youtubeId = '';
                if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $activeLesson->youtube_url, $match)) {
                    $youtubeId = $match[1];
                }
            @endphp
            
            @if($youtubeId)
                <div class="w-full h-full relative" style="padding-bottom: 56.25%;">
                    <iframe 
                        class="absolute top-0 left-0 w-full h-full"
                        src="https://www.youtube.com/embed/{{ $youtubeId }}?rel=0&modestbranding=1&showinfo=0" 
                        title="YouTube video player" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                        allowfullscreen>
                    </iframe>
                </div>
            @else
                <div class="flex-1 flex flex-col items-center justify-center text-center p-8 bg-gray-900 text-white h-full">
                    <i class="fab fa-youtube text-5xl text-gray-600 mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Invalid Video URL</h3>
                    <p class="text-gray-400 max-w-md">The YouTube video URL provided for this lesson appears to be invalid.</p>
                </div>
            @endif
            
        @elseif($activeLesson && $activeLesson->vimeo_url)
            @php
                // Extract Vimeo Video ID
                $vimeoId = '';
                if (preg_match('/(?:www\.|player\.)?vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)/i', $activeLesson->vimeo_url, $match)) {
                    $vimeoId = $match[1];
                }
            @endphp
            
            @if($vimeoId)
                <div class="w-full h-full relative" style="padding-bottom: 56.25%;">
                    <iframe 
                        class="absolute top-0 left-0 w-full h-full"
                        src="https://player.vimeo.com/video/{{ $vimeoId }}?title=0&byline=0&portrait=0&badge=0" 
                        title="Vimeo video player" 
                        frameborder="0" 
                        allow="autoplay; fullscreen; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                </div>
            @else
                <div class="flex-1 flex flex-col items-center justify-center text-center p-8 bg-gray-900 text-white h-full">
                    <i class="fab fa-vimeo text-5xl text-gray-600 mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Invalid Video URL</h3>
                    <p class="text-gray-400 max-w-md">The Vimeo video URL provided for this lesson appears to be invalid.</p>
                </div>
            @endif
            
            <!-- Video Info Bar -->
            <div class="bg-white p-6 border-t border-gray-200">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $activeLesson->title }}</h2>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $activeLesson->description ?? 'No description provided for this lesson.' }}</p>
                    </div>
                    @if($activeLesson->duration)
                        <div class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 text-xs font-semibold">
                            <i class="far fa-clock"></i> {{ $activeLesson->duration }}
                        </div>
                    @endif
                </div>
            </div>
            
        @else
            <div class="flex-1 flex flex-col items-center justify-center text-center p-8 bg-gray-900 text-white h-full">
                <div class="w-20 h-20 bg-gray-800 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-film text-3xl text-gray-600"></i>
                </div>
                <h3 class="text-2xl font-bold mb-2">No lesson selected or available</h3>
                <p class="text-gray-400 max-w-md">There are no lessons available to watch right now.</p>
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
                @endphp
                <a href="{{ route('student.courses.show', [$course->id, $lesson->id]) }}" 
                   class="block p-3 rounded-xl border transition-all duration-200 group relative overflow-hidden {{ $isActive ? 'bg-indigo-50 border-indigo-200 shadow-[0_2px_8px_rgb(79,70,229,0.1)]' : 'bg-white border-transparent hover:bg-gray-50 hover:border-gray-200' }}">
                   
                   @if($isActive)
                       <!-- Active Indicator Line -->
                       <div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-600"></div>
                   @endif
                   
                    <div class="flex items-start gap-3 pl-1">
                        <div class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center {{ $isActive ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-400 group-hover:bg-indigo-50 group-hover:text-indigo-500' }} transition-colors">
                            @if($isActive)
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
                                    <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                    <span class="text-[11px] text-gray-500 flex items-center gap-1">
                                        <i class="far fa-clock"></i> {{ $lesson->duration }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="p-6 text-center text-gray-500">
                    <i class="fas fa-folder-open text-3xl text-gray-300 mb-3"></i>
                    <p class="text-sm font-medium">No lessons have been added to this course yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
