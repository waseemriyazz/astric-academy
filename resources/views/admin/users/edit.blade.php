@extends('layouts.admin')

@section('header', 'Edit Student')

@section('content')
<div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
    <div class="flex items-center justify-between mb-8">
        <h3 class="text-xl font-bold text-gray-900">Edit Student Details</h3>
        <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-gray-500 hover:text-brand-600 transition">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>
    
    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <input type="text" name="name" required value="{{ old('name', $user->name) }}" class="w-full rounded-2xl border-gray-300 focus:ring-brand-500 focus:border-brand-500">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
            <input type="email" name="email" required value="{{ old('email', $user->email) }}" class="w-full rounded-2xl border-gray-300 focus:ring-brand-500 focus:border-brand-500">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
            <label class="block text-sm font-medium text-gray-700 mb-1">Update Password</label>
            <p class="text-xs text-gray-500 mb-3">Leave blank if you do not wish to change the password.</p>
            <div class="relative">
                <input type="password" id="password_input" name="password" class="w-full rounded-2xl border-gray-300 focus:ring-brand-500 focus:border-brand-500 pr-10" placeholder="New password...">
                <button type="button" onclick="const p = document.getElementById('password_input'); const i = document.getElementById('eye_icon'); if(p.type === 'password'){ p.type = 'text'; i.classList.remove('fa-eye'); i.classList.add('fa-eye-slash'); } else { p.type = 'password'; i.classList.remove('fa-eye-slash'); i.classList.add('fa-eye'); }" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                    <i id="eye_icon" class="fas fa-eye"></i>
                </button>
            </div>
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="pt-4 border-t border-gray-100 mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">Manage Course Assignments</label>
            <div class="space-y-3">
                @foreach($courses as $course)
                <label class="flex items-start gap-3 p-4 border {{ $user->courses->contains($course->id) ? 'border-brand-200 bg-brand-50/30' : 'border-gray-200 hover:bg-gray-50' }} rounded-2xl cursor-pointer transition">
                    <input type="checkbox" name="courses[]" value="{{ $course->id }}" {{ $user->courses->contains($course->id) ? 'checked' : '' }} class="mt-0.5 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    <div>
                        <p class="text-sm font-bold text-gray-900">{{ $course->title }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ Str::limit($course->description, 80) }}</p>
                    </div>
                </label>
                @endforeach
            </div>
            @error('courses') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end gap-3 pt-6">
            <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 bg-white border border-gray-300 rounded-2xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Cancel</a>
            <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 rounded-2xl text-sm font-medium text-white transition shadow-sm">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
