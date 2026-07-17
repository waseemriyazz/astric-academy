@extends('layouts.admin')

@section('header', 'Edit Testimonial')

@section('content')
<div class="max-w-2xl bg-white rounded-xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 p-8">
    <div class="flex items-center justify-between mb-8 pb-6 border-b border-gray-100">
        <div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">Edit Testimonial</h3>
            <p class="text-sm text-gray-500">Update student success story.</p>
        </div>
        <a href="{{ route('admin.testimonials.index') }}" class="text-sm font-medium text-gray-500 hover:text-blue-600 transition flex items-center gap-1">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
    
    <form action="{{ route('admin.testimonials.update', $testimonial->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Student Name *</label>
            <input type="text" name="name" required value="{{ old('name', $testimonial->name) }}" class="w-full rounded-lg border-gray-200 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Role / Designation *</label>
            <input type="text" name="role" required value="{{ old('role', $testimonial->role) }}" class="w-full rounded-lg border-gray-200 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
            @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Testimonial Content *</label>
            <textarea name="content" rows="4" required class="w-full rounded-lg border-gray-200 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">{{ old('content', $testimonial->content) }}</textarea>
            @error('content') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Image URL</label>
                <input type="url" name="image_url" value="{{ old('image_url', $testimonial->image_url) }}" class="w-full rounded-lg border-gray-200 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                <p class="mt-1 text-xs text-gray-500">Optional. URL to student's photo.</p>
                @error('image_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Sort Order</label>
                <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $testimonial->sort_order) }}" class="w-full rounded-lg border-gray-200 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                <p class="mt-1 text-xs text-gray-500">Lower numbers appear first.</p>
                @error('sort_order') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="pt-6 border-t border-gray-100 flex items-center justify-end gap-3">
            <a href="{{ route('admin.testimonials.index') }}" class="px-5 py-2 text-gray-600 font-medium hover:bg-gray-50 border border-gray-200 rounded-lg transition text-sm">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-5 rounded-lg transition shadow-sm text-sm">
                <i class="fas fa-save"></i> Update Testimonial
            </button>
        </div>
    </form>
</div>
@endsection