@extends('layouts.admin')

@section('header', 'Create FAQ')

@section('content')
<div class="max-w-2xl bg-white rounded-xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 p-8">
    <div class="flex items-center justify-between mb-8 pb-6 border-b border-gray-100">
        <div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">New FAQ</h3>
            <p class="text-sm text-gray-500">Add a frequently asked question.</p>
        </div>
        <a href="{{ route('admin.faqs.index') }}" class="text-sm font-medium text-gray-500 hover:text-blue-600 transition flex items-center gap-1">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
    
    <form action="{{ route('admin.faqs.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Question *</label>
            <input type="text" name="question" required value="{{ old('question') }}" class="w-full rounded-lg border-gray-200 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm" placeholder="e.g. Are the courses self-paced or live?">
            @error('question') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Answer *</label>
            <textarea name="answer" rows="4" required class="w-full rounded-lg border-gray-200 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm" placeholder="e.g. We offer both! Most courses are self-paced with weekly live mentorship sessions.">{{ old('answer') }}</textarea>
            @error('answer') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Sort Order</label>
            <input type="number" name="sort_order" min="0" value="{{ old('sort_order', '0') }}" class="w-full rounded-lg border-gray-200 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
            <p class="mt-1 text-xs text-gray-500">Lower numbers appear first.</p>
            @error('sort_order') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="pt-6 border-t border-gray-100 flex items-center justify-end gap-3">
            <a href="{{ route('admin.faqs.index') }}" class="px-5 py-2 text-gray-600 font-medium hover:bg-gray-50 border border-gray-200 rounded-lg transition text-sm">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-5 rounded-lg transition shadow-sm text-sm">
                <i class="fas fa-save"></i> Save FAQ
            </button>
        </div>
    </form>
</div>
@endsection