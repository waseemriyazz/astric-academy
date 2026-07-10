@extends('layouts.admin')

@section('header', 'Edit Lesson: ' . $lesson->title)

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.courses.lessons.index', $course->id) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center gap-2 transition">
        <i class="fas fa-arrow-left"></i> Back to Lessons
    </a>
</div>

<div class="flex flex-col lg:flex-row gap-8">
    <div class="w-full lg:w-2/3 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Edit Lesson Details</h3>
        
        <form action="{{ route('admin.courses.lessons.update', [$course->id, $lesson->id]) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lesson Title</label>
                <input type="text" name="title" required value="{{ old('title', $lesson->title) }}" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">YouTube URL</label>
                <input type="url" name="youtube_url" value="{{ old('youtube_url', $lesson->youtube_url) }}" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                @error('youtube_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-gray-400">Provide the full YouTube video link (e.g. https://www.youtube.com/watch?v=...)</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vimeo URL</label>
                <input type="url" name="vimeo_url" value="{{ old('vimeo_url', $lesson->vimeo_url) }}" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                @error('vimeo_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-gray-400">Provide the full Vimeo video link (e.g. https://vimeo.com/...)</p>
            </div>

            <div class="flex gap-4">
                <div class="w-1/2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Duration</label>
                    <input type="text" name="duration" value="{{ old('duration', $lesson->duration) }}" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('duration') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="w-1/2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                    <input type="number" name="order" value="{{ old('order', $lesson->order) }}" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('order') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                <textarea name="description" rows="5" class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $lesson->description) }}</textarea>
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full mt-6 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-lg transition shadow-sm flex items-center justify-center gap-2">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </form>
    </div>

    <!-- Preview Video -->
    <div class="w-full lg:w-1/3 space-y-6">
        @if($lesson->youtube_url)
            @php
                // Try to extract YouTube ID
                preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $lesson->youtube_url, $match);
                $youtube_id = $match[1] ?? null;
            @endphp

            @if($youtube_id)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">YouTube Preview</h3>
                    <div class="relative w-full rounded-xl overflow-hidden" style="padding-bottom: 56.25%;">
                        <iframe class="absolute top-0 left-0 w-full h-full" src="https://www.youtube.com/embed/{{ $youtube_id }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                </div>
            @endif
        @endif
        
        @if($lesson->vimeo_url)
            @php
                // Try to extract Vimeo ID
                preg_match('/(?:www\.|player\.)?vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)/i', $lesson->vimeo_url, $match);
                $vimeo_id = $match[1] ?? null;
            @endphp

            @if($vimeo_id)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Vimeo Preview</h3>
                    <div class="relative w-full rounded-xl overflow-hidden" style="padding-bottom: 56.25%;">
                        <iframe class="absolute top-0 left-0 w-full h-full" src="https://player.vimeo.com/video/{{ $vimeo_id }}?title=0&byline=0&portrait=0&badge=0" title="Vimeo video player" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
