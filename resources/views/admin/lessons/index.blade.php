@extends('layouts.admin')

@section('header', 'Manage Lessons: ' . $course->title)

@section('header_actions')
<a href="{{ route('admin.courses.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium shadow-[0_2px_10px_rgb(37,99,235,0.2)] hover:bg-blue-700 transition flex items-center gap-2 text-sm">
    <i class="fas fa-arrow-left"></i> Back to Courses
</a>
@endsection

@section('content')
<div class="flex flex-col xl:flex-row gap-6">
    <!-- List of Lessons -->
    <div class="w-full xl:w-2/3">
        <div class="bg-white rounded-xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 overflow-hidden">
            
            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/30">
                <h3 class="text-base font-bold text-gray-900">Curriculum</h3>
                <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-xs font-semibold uppercase tracking-wider">{{ $lessons->count() }} Lessons</span>
            </div>
            
            <div class="p-0 overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-max">
                    <thead>
                        <tr class="bg-white text-gray-500 text-xs uppercase tracking-wider font-semibold border-b border-gray-100">
                            <th class="px-6 py-4 w-16">#</th>
                            <th class="px-6 py-4">Lesson Details</th>
                            <th class="px-6 py-4 text-center">Video</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-sm">
                        @forelse($lessons as $lesson)
                        <tr class="hover:bg-gray-50/80 transition-all duration-300">
                            <td class="px-6 py-4 font-bold text-gray-400">
                                {{ str_pad($lesson->order, 2, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-500 font-bold shadow-sm">
                                        <i class="fas fa-book-open text-sm"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900 text-base mb-1">{{ $lesson->title }}</h4>
                                        <p class="text-gray-500 text-xs font-medium flex items-center gap-2">
                                            <i class="far fa-clock text-gray-400"></i> {{ $lesson->duration ?? 'Duration not set' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($lesson->youtube_url)
                                    <a href="{{ $lesson->youtube_url }}" target="_blank" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all shadow-sm" title="Watch YouTube Video">
                                        <i class="fab fa-youtube text-sm"></i>
                                    </a>
                                @elseif($lesson->vimeo_url)
                                    <a href="{{ $lesson->vimeo_url }}" target="_blank" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-500 hover:bg-blue-500 hover:text-white transition-all shadow-sm" title="Watch Vimeo Video">
                                        <i class="fab fa-vimeo text-sm"></i>
                                    </a>
                                @else
                                    <span class="inline-block w-2 h-2 rounded-full bg-gray-300" title="No video attached"></span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.courses.lessons.edit', [$course->id, $lesson->id]) }}" class="w-8 h-8 rounded bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all duration-300 shadow-sm" title="Edit Lesson">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.courses.lessons.destroy', [$course->id, $lesson->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lesson?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 rounded bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-all duration-300 shadow-sm" title="Delete Lesson">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-50 mb-3 text-gray-400 border border-gray-100">
                                    <i class="fas fa-film text-xl"></i>
                                </div>
                                <h3 class="text-gray-900 font-medium mb-1">No lessons yet</h3>
                                <p class="text-gray-500 text-sm">Start building your curriculum by adding a lesson.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Lesson Form -->
    <div class="w-full xl:w-1/3">
        <div class="bg-white rounded-xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 p-6 sticky top-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 shadow-sm border border-blue-100">
                    <i class="fas fa-plus text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Add Module</h3>
                    <p class="text-xs text-gray-500">Add a new lesson or video.</p>
                </div>
            </div>
            
            <form action="{{ route('admin.courses.lessons.store', $course->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Lesson Title *</label>
                    <input type="text" name="title" required value="{{ old('title') }}" class="w-full rounded-lg border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-blue-500 focus:border-blue-500 transition shadow-sm" placeholder="e.g. Introduction">
                    @error('title') <p class="mt-1 text-xs font-semibold text-red-500">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">YouTube URL</label>
                    <div class="relative flex items-center">
                        <div class="absolute left-3 text-red-500"><i class="fab fa-youtube"></i></div>
                        <input type="url" name="youtube_url" value="{{ old('youtube_url') }}" class="w-full pl-9 pr-3 py-2 rounded-lg border-gray-200 bg-gray-50 text-sm text-gray-900 focus:bg-white focus:ring-blue-500 focus:border-blue-500 transition shadow-sm" placeholder="https://youtube.com/...">
                    </div>
                    @error('youtube_url') <p class="mt-1 text-xs font-semibold text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Vimeo URL</label>
                    <div class="relative flex items-center">
                        <div class="absolute left-3 text-blue-400"><i class="fab fa-vimeo"></i></div>
                        <input type="url" name="vimeo_url" value="{{ old('vimeo_url') }}" class="w-full pl-9 pr-3 py-2 rounded-lg border-gray-200 bg-gray-50 text-sm text-gray-900 focus:bg-white focus:ring-blue-500 focus:border-blue-500 transition shadow-sm" placeholder="https://vimeo.com/...">
                    </div>
                    @error('vimeo_url') <p class="mt-1 text-xs font-semibold text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-4">
                    <div class="w-1/2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Duration</label>
                        <input type="text" name="duration" value="{{ old('duration') }}" class="w-full rounded-lg border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-blue-500 focus:border-blue-500 transition shadow-sm" placeholder="e.g. 15 mins">
                        @error('duration') <p class="mt-1 text-xs font-semibold text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="w-1/2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Sort Order</label>
                        <input type="number" name="order" value="{{ old('order', 0) }}" class="w-full rounded-lg border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                        @error('order') <p class="mt-1 text-xs font-semibold text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full rounded-lg border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-blue-500 focus:border-blue-500 transition shadow-sm resize-none" placeholder="What will they learn?">{{ old('description') }}</textarea>
                    @error('description') <p class="mt-1 text-xs font-semibold text-red-500">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full mt-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg transition shadow-sm flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-plus"></i>
                    <span>Add Lesson</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
