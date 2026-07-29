@extends('layouts.admin')

@section('header', 'Edit Lesson: ' . $lesson->title)

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.courses.lessons.index', $course->id) }}" class="text-brand-600 hover:text-brand-800 text-sm font-medium flex items-center gap-2 transition">
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
                <input type="text" name="title" required value="{{ old('title', $lesson->title) }}" class="w-full rounded-2xl border-gray-300 focus:ring-brand-500 focus:border-brand-500">
                @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Plan Assignment -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assign to Plan</label>
                <select name="plan_id" class="w-full rounded-2xl border-gray-300 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">Course-level (all plans)</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" {{ old('plan_id', $lesson->plan_id) == $plan->id ? 'selected' : '' }}>
                            {{ $plan->tier_name }} (${{ number_format($plan->price, 0) }})
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-400">Leave as "Course-level" if this lesson belongs to all plans.</p>
                @error('plan_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">YouTube URL</label>
                <input type="url" name="youtube_url" value="{{ old('youtube_url', $lesson->youtube_url) }}" class="w-full rounded-2xl border-gray-300 focus:ring-brand-500 focus:border-brand-500">
                @error('youtube_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-gray-400">Provide the full YouTube video link (e.g. https://www.youtube.com/watch?v=...)</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vimeo URL</label>
                <input type="url" name="vimeo_url" value="{{ old('vimeo_url', $lesson->vimeo_url) }}" class="w-full rounded-2xl border-gray-300 focus:ring-brand-500 focus:border-brand-500">
                @error('vimeo_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <p class="mt-1 text-xs text-gray-400">Provide the full Vimeo video link (e.g. https://vimeo.com/...)</p>
            </div>

            <div class="flex gap-4">
                <div class="w-1/2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Duration</label>
                    <input type="text" name="duration" value="{{ old('duration', $lesson->duration) }}" class="w-full rounded-2xl border-gray-300 focus:ring-brand-500 focus:border-brand-500">
                    @error('duration') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="w-1/2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                    <input type="number" name="order" value="{{ old('order', $lesson->order) }}" class="w-full rounded-2xl border-gray-300 focus:ring-brand-500 focus:border-brand-500">
                    @error('order') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                <textarea name="description" rows="5" class="w-full rounded-2xl border-gray-300 focus:ring-brand-500 focus:border-brand-500">{{ old('description', $lesson->description) }}</textarea>
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Summary (Optional) <span class="text-brand-400 text-[10px] font-medium">Supports Markdown</span></label>
                <textarea name="summary" rows="3" class="w-full rounded-2xl border-gray-300 focus:ring-brand-500 focus:border-brand-500" placeholder="Brief overview or key takeaways... You can use **bold**, *italic*, - lists, ## headings, etc.">{{ old('summary', $lesson->summary) }}</textarea>
                @error('summary') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Quiz Section -->
            <div class="mt-8 pt-6 border-t-2 border-gray-200">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-2xl bg-purple-50 border border-purple-200 flex items-center justify-center text-purple-600">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Lesson Quiz</h3>
                        <p class="text-xs text-gray-500">Set an MCQ quiz for this lesson. Students must attempt it to unlock the next lesson.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Question</label>
                        <input type="text" name="quiz_question" value="{{ old('quiz_question', $lesson->quiz->question ?? '') }}" class="w-full rounded-2xl border-gray-300 focus:ring-purple-500 focus:border-purple-500" placeholder="e.g. What is the capital of France?">
                        @error('quiz_question') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Option A</label>
                            <input type="text" name="quiz_option_a" value="{{ old('quiz_option_a', $lesson->quiz->option_a ?? '') }}" class="w-full rounded-2xl border-gray-300 focus:ring-purple-500 focus:border-purple-500" placeholder="Option A">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Option B</label>
                            <input type="text" name="quiz_option_b" value="{{ old('quiz_option_b', $lesson->quiz->option_b ?? '') }}" class="w-full rounded-2xl border-gray-300 focus:ring-purple-500 focus:border-purple-500" placeholder="Option B">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Option C</label>
                            <input type="text" name="quiz_option_c" value="{{ old('quiz_option_c', $lesson->quiz->option_c ?? '') }}" class="w-full rounded-2xl border-gray-300 focus:ring-purple-500 focus:border-purple-500" placeholder="Option C">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Option D</label>
                            <input type="text" name="quiz_option_d" value="{{ old('quiz_option_d', $lesson->quiz->option_d ?? '') }}" class="w-full rounded-2xl border-gray-300 focus:ring-purple-500 focus:border-purple-500" placeholder="Option D">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Correct Answer</label>
                        <select name="quiz_correct_answer" class="w-full rounded-2xl border-gray-300 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- No quiz / Remove quiz --</option>
                            <option value="a" {{ old('quiz_correct_answer', $lesson->quiz->correct_answer ?? '') == 'a' ? 'selected' : '' }}>A</option>
                            <option value="b" {{ old('quiz_correct_answer', $lesson->quiz->correct_answer ?? '') == 'b' ? 'selected' : '' }}>B</option>
                            <option value="c" {{ old('quiz_correct_answer', $lesson->quiz->correct_answer ?? '') == 'c' ? 'selected' : '' }}>C</option>
                            <option value="d" {{ old('quiz_correct_answer', $lesson->quiz->correct_answer ?? '') == 'd' ? 'selected' : '' }}>D</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-400">Select an answer to set a quiz. Leave "No quiz" to remove any existing quiz.</p>
                        @error('quiz_correct_answer') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full mt-6 bg-brand-600 hover:bg-brand-700 text-white font-medium py-2.5 rounded-2xl transition shadow-sm flex items-center justify-center gap-2">
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
                    <div class="relative w-full rounded-2xl overflow-hidden" style="padding-bottom: 56.25%;">
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
                    <div class="relative w-full rounded-2xl overflow-hidden" style="padding-bottom: 56.25%;">
                        <iframe class="absolute top-0 left-0 w-full h-full" src="https://player.vimeo.com/video/{{ $vimeo_id }}?title=0&byline=0&portrait=0&badge=0" title="Vimeo video player" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
