@extends('layouts.admin')

@section('header', 'Create New Plan')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-900">Plan Details</h3>
        </div>
        
        <form action="{{ route('admin.plans.store') }}" method="POST" class="p-6 space-y-5">
            @csrf

            <!-- Course Selection -->
            <div>
                <label for="course_id" class="block text-sm font-semibold text-gray-700 mb-1.5">Course *</label>
                <select name="course_id" id="course_id" required
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-blue-500 focus:border-blue-500 @error('course_id') border-red-500 @enderror">
                    <option value="">Select a course</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ (string)$selectedCourseId === (string)$course->id ? 'selected' : '' }}>
                            {{ $course->title }}
                        </option>
                    @endforeach
                </select>
                @error('course_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tier Name -->
            <div>
                <label for="tier_name" class="block text-sm font-semibold text-gray-700 mb-1.5">Tier Name *</label>
                <input type="text" name="tier_name" id="tier_name" value="{{ old('tier_name') }}" required
                    placeholder="e.g., Basic, Intermediate, Advanced, Pro"
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-blue-500 focus:border-blue-500 @error('tier_name') border-red-500 @enderror">
                @error('tier_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-1.5">Description</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                    placeholder="Describe what this plan offers...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Price & Duration Row -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="price" class="block text-sm font-semibold text-gray-700 mb-1.5">Price *</label>
                    <input type="number" name="price" id="price" value="{{ old('price', 0) }}" required step="0.01" min="0"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-blue-500 focus:border-blue-500 @error('price') border-red-500 @enderror">
                    @error('price')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="duration" class="block text-sm font-semibold text-gray-700 mb-1.5">Duration</label>
                    <input type="text" name="duration" id="duration" value="{{ old('duration') }}"
                        placeholder="e.g., 3 Months"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-blue-500 focus:border-blue-500 @error('duration') border-red-500 @enderror">
                    @error('duration')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Sort Order & Active Status Row -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="sort_order" class="block text-sm font-semibold text-gray-700 mb-1.5">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
                    <div class="flex items-center gap-3 mt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div>
                <label for="features" class="block text-sm font-semibold text-gray-700 mb-1.5">Features (one per line)</label>
                <textarea name="features" id="features" rows="5"
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-blue-500 focus:border-blue-500 font-mono"
                    placeholder="Feature 1&#10;Feature 2&#10;Feature 3">{{ old('features') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Enter each feature on a new line.</p>
            </div>

            <!-- Includes -->
            <div>
                <label for="includes" class="block text-sm font-semibold text-gray-700 mb-1.5">What's Included (one per line)</label>
                <textarea name="includes" id="includes" rows="5"
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:ring-blue-500 focus:border-blue-500 font-mono"
                    placeholder="Item 1&#10;Item 2&#10;Item 3">{{ old('includes') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Enter each included item on a new line.</p>
            </div>

            <!-- Submit -->
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-medium shadow-[0_2px_10px_rgb(37,99,235,0.2)] hover:bg-blue-700 transition text-sm">
                    <i class="fas fa-save mr-1.5"></i> Create Plan
                </button>
                <a href="{{ route('admin.plans.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition text-sm">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection