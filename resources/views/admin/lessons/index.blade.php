@extends('layouts.admin')

@section('header', 'Manage Lessons: ' . $course->title)

@section('header_actions')
<a href="{{ route('admin.courses.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-2xl font-medium shadow-[0_2px_10px_rgb(37,99,235,0.2)] hover:bg-blue-700 transition flex items-center gap-2 text-sm">
    <i class="fas fa-arrow-left"></i> Back to Courses
</a>
@endsection

@section('content')
<!-- Plan Filter Bar -->
<div class="bg-white rounded-2xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 p-4 mb-6">
    <form method="GET" action="{{ route('admin.courses.lessons.index', $course->id) }}" class="flex items-center gap-4 flex-wrap">
        <label for="plan_id" class="text-sm font-semibold text-gray-700">Filter by Plan:</label>
        <select name="plan_id" id="plan_id" onchange="this.form.submit()" class="border border-gray-200 rounded-2xl px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Lessons (Course-level)</option>
            @foreach($plans as $plan)
                <option value="{{ $plan->id }}" {{ (string)$planId === (string)$plan->id ? 'selected' : '' }}>
                    {{ $plan->tier_name }}
                </option>
            @endforeach
        </select>
        @if($planId)
            <a href="{{ route('admin.courses.lessons.index', $course->id) }}" class="text-sm text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i> Clear
            </a>
        @endif
    </form>
</div>

<div class="flex flex-col xl:flex-row gap-6">
    <!-- List of Lessons -->
    <div class="w-full xl:w-2/3">
        <div class="bg-white rounded-2xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 overflow-hidden">
            
            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/30">
                <h3 class="text-base font-bold text-gray-900">Curriculum</h3>
                <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-2xl text-xs font-semibold uppercase tracking-wider">{{ $lessons->count() }} Lessons</span>
            </div>
            
            <div class="p-0 overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-max">
                    <thead>
                        <tr class="bg-white text-gray-500 text-xs uppercase tracking-wider font-semibold border-b border-gray-100">
                            <th class="px-6 py-4 w-16">#</th>
                            <th class="px-6 py-4">Lesson Details</th>
                            <th class="px-6 py-4">Plan</th>
                            <th class="px-6 py-4 text-center">Video</th>
                            <th class="px-6 py-4 text-center">Quiz</th>
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
                                    <div class="w-10 h-10 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-500 font-bold shadow-sm">
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
                            <td class="px-6 py-4">
                                @if($lesson->plan)
                                    <span class="inline-flex px-2.5 py-1 rounded bg-purple-50 text-purple-700 text-xs font-medium border border-purple-100/50">
                                        {{ $lesson->plan->tier_name }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">Course-level</span>
                                @endif
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
                            <td class="px-6 py-4 text-center">
                                @if($lesson->quiz)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-50 text-green-600 text-xs font-semibold border border-green-200">
                                        <i class="fas fa-check-circle text-[10px]"></i> Quiz
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-gray-50 text-gray-400 text-xs font-semibold border border-gray-200">
                                        <i class="fas fa-times-circle text-[10px]"></i> None
                                    </span>
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
                            <td colspan="6" class="px-6 py-12 text-center">
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
        <div class="bg-white rounded-2xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 p-6 sticky top-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-sm border border-blue-100">
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
                    <input type="text" name="title" required value="{{ old('title') }}" class="w-full rounded-2xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-blue-500 focus:border-blue-500 transition shadow-sm" placeholder="e.g. Introduction">
                    @error('title') <p class="mt-1 text-xs font-semibold text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Plan Assignment -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Assign to Plan</label>
                    <select name="plan_id" class="w-full rounded-2xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                        <option value="">Course-level (all plans)</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->tier_name }} (${{ number_format($plan->price, 0) }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Leave as "Course-level" if this lesson belongs to all plans.</p>
                    @error('plan_id') <p class="mt-1 text-xs font-semibold text-red-500">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">YouTube URL</label>
                    <div class="relative flex items-center">
                        <div class="absolute left-3 text-red-500"><i class="fab fa-youtube"></i></div>
                        <input type="url" name="youtube_url" value="{{ old('youtube_url') }}" class="w-full pl-9 pr-3 py-2 rounded-2xl border-gray-200 bg-gray-50 text-sm text-gray-900 focus:bg-white focus:ring-blue-500 focus:border-blue-500 transition shadow-sm" placeholder="https://youtube.com/...">
                    </div>
                    @error('youtube_url') <p class="mt-1 text-xs font-semibold text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Vimeo URL</label>
                    <div class="relative flex items-center">
                        <div class="absolute left-3 text-blue-400"><i class="fab fa-vimeo"></i></div>
                        <input type="url" name="vimeo_url" value="{{ old('vimeo_url') }}" class="w-full pl-9 pr-3 py-2 rounded-2xl border-gray-200 bg-gray-50 text-sm text-gray-900 focus:bg-white focus:ring-blue-500 focus:border-blue-500 transition shadow-sm" placeholder="https://vimeo.com/...">
                    </div>
                    @error('vimeo_url') <p class="mt-1 text-xs font-semibold text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-4">
                    <div class="w-1/2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Duration</label>
                        <input type="text" name="duration" value="{{ old('duration') }}" class="w-full rounded-2xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-blue-500 focus:border-blue-500 transition shadow-sm" placeholder="e.g. 15 mins">
                        @error('duration') <p class="mt-1 text-xs font-semibold text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="w-1/2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Sort Order</label>
                        <input type="number" name="order" value="{{ old('order', 0) }}" class="w-full rounded-2xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                        @error('order') <p class="mt-1 text-xs font-semibold text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full rounded-2xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-blue-500 focus:border-blue-500 transition shadow-sm resize-none" placeholder="What will they learn?">{{ old('description') }}</textarea>
                    @error('description') <p class="mt-1 text-xs font-semibold text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Summary <span class="text-gray-400 font-normal">(Optional) <span class="text-brand-400 text-[10px] font-medium">Supports Markdown</span></span></label>
                    <textarea name="summary" rows="2" class="w-full rounded-2xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-blue-500 focus:border-blue-500 transition shadow-sm resize-none" placeholder="Brief overview or key takeaways... You can use **bold**, *italic*, - lists, ## headings, etc.">{{ old('summary') }}</textarea>
                    @error('summary') <p class="mt-1 text-xs font-semibold text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Quiz Section -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 rounded-2xl bg-purple-50 flex items-center justify-center text-purple-600">
                            <i class="fas fa-question-circle text-sm"></i>
                        </div>
                        <h4 class="text-sm font-bold text-gray-900">Quiz (Optional)</h4>
                    </div>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Question</label>
                            <input type="text" name="quiz_question" value="{{ old('quiz_question') }}" class="w-full rounded-2xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-purple-500 focus:border-purple-500 transition shadow-sm" placeholder="e.g. What is 2 + 2?">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Option A</label>
                                <input type="text" name="quiz_option_a" value="{{ old('quiz_option_a') }}" class="w-full rounded-2xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-purple-500 focus:border-purple-500 transition shadow-sm" placeholder="Option A">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Option B</label>
                                <input type="text" name="quiz_option_b" value="{{ old('quiz_option_b') }}" class="w-full rounded-2xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-purple-500 focus:border-purple-500 transition shadow-sm" placeholder="Option B">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Option C</label>
                                <input type="text" name="quiz_option_c" value="{{ old('quiz_option_c') }}" class="w-full rounded-2xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-purple-500 focus:border-purple-500 transition shadow-sm" placeholder="Option C">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Option D</label>
                                <input type="text" name="quiz_option_d" value="{{ old('quiz_option_d') }}" class="w-full rounded-2xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-purple-500 focus:border-purple-500 transition shadow-sm" placeholder="Option D">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Correct Answer</label>
                            <select name="quiz_correct_answer" class="w-full rounded-2xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:bg-white focus:ring-purple-500 focus:border-purple-500 transition shadow-sm">
                                <option value="">-- Select --</option>
                                <option value="a" {{ old('quiz_correct_answer') == 'a' ? 'selected' : '' }}>A</option>
                                <option value="b" {{ old('quiz_correct_answer') == 'b' ? 'selected' : '' }}>B</option>
                                <option value="c" {{ old('quiz_correct_answer') == 'c' ? 'selected' : '' }}>C</option>
                                <option value="d" {{ old('quiz_correct_answer') == 'd' ? 'selected' : '' }}>D</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full mt-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-2xl transition shadow-sm flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-plus"></i>
                    <span>Add Lesson</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
