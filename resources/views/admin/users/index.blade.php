@extends('layouts.admin')

@section('header', 'Manage Students')

@section('header_actions')
<button class="px-4 py-2 bg-blue-600 text-white rounded-2xl font-medium shadow-[0_2px_10px_rgb(37,99,235,0.2)] hover:bg-blue-700 transition flex items-center gap-2 text-sm">
    <i class="fas fa-plus"></i> Add Student
</button>
@endsection

@section('content')
<!-- Filter bar -->
<div class="bg-white rounded-2xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 p-4 mb-6 flex flex-col sm:flex-row gap-4 items-center justify-between">
    <div class="relative w-full sm:w-96">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400 text-sm"></i>
        </div>
        <input type="text" class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-2xl text-sm focus:ring-blue-500 focus:border-blue-500 transition placeholder-gray-400" placeholder="Search by student name or email...">
    </div>
    <div class="flex items-center gap-3 w-full sm:w-auto">
        <select class="w-full sm:w-auto border border-gray-200 rounded-2xl text-sm py-2 px-3 focus:ring-blue-500 focus:border-blue-500 text-gray-600 outline-none">
            <option>All Courses</option>
        </select>
        <select class="w-full sm:w-auto border border-gray-200 rounded-2xl text-sm py-2 px-3 focus:ring-blue-500 focus:border-blue-500 text-gray-600 outline-none hidden sm:block">
            <option>All Status</option>
        </select>
        <button class="px-4 py-2 border border-gray-200 text-gray-600 rounded-2xl font-medium hover:bg-gray-50 transition flex items-center gap-2 text-sm whitespace-nowrap">
            <i class="fas fa-filter text-gray-400"></i> Filters
        </button>
    </div>
</div>

<div class="flex flex-col lg:flex-row gap-8">
    <!-- List of Students -->
    <div class="w-full lg:w-2/3 bg-white rounded-2xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 overflow-hidden">
        <div class="p-0 overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-max">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-500 text-xs font-semibold uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4">Student</th>
                        <th class="px-6 py-4">Enrolled Courses</th>
                        <th class="px-6 py-4">Joined</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($students as $student)
                    <tr class="hover:bg-gray-50/50 transition group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-100 shrink-0">
                                    {{ substr($student->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $student->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $student->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($student->courses as $course)
                                    <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded text-[11px] font-medium border border-blue-100/50">
                                        {{ $course->title }}
                                    </span>
                                @empty
                                    <span class="text-gray-400 italic text-[11px]">No courses</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-gray-900 font-medium">{{ $student->created_at->format('d M Y') }}</p>
                            <p class="text-gray-500 text-xs mt-0.5">{{ $student->created_at->format('h:i A') }}</p>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('admin.users.edit', $student->id) }}" class="w-8 h-8 rounded flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition" title="Edit Student">
                                    <i class="fas fa-pen text-xs"></i>
                                </a>
                                <form action="{{ route('admin.users.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this student?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 transition" title="Delete Student">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-50 mb-3 text-gray-400">
                                <i class="fas fa-user-slash text-xl"></i>
                            </div>
                            <p class="text-gray-500 font-medium">No students found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($students->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
            {{ $students->links() }}
        </div>
        @endif
    </div>

    <!-- Registration Form -->
    <div class="w-full lg:w-1/3">
        <div class="bg-white rounded-2xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 p-6 sticky top-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-8 rounded bg-blue-50 flex items-center justify-center text-blue-600 border border-blue-100 shrink-0">
                    <i class="fas fa-user-plus text-sm"></i>
                </div>
                <h3 class="text-base font-bold text-gray-900">Manually Register</h3>
            </div>
            
            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Full Name</label>
                    <input type="text" name="name" required class="w-full rounded-2xl border-gray-200 text-sm focus:ring-blue-500 focus:border-blue-500 py-2 shadow-sm">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Email Address</label>
                    <input type="email" name="email" required class="w-full rounded-2xl border-gray-200 text-sm focus:ring-blue-500 focus:border-blue-500 py-2 shadow-sm">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1.5">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="reg_password" required class="w-full rounded-2xl border-gray-200 text-sm focus:ring-blue-500 focus:border-blue-500 py-2 shadow-sm pr-10">
                        <button type="button" onclick="const p = document.getElementById('reg_password'); const i = this.querySelector('i'); if(p.type === 'password'){ p.type = 'text'; i.classList.remove('fa-eye-slash'); i.classList.add('fa-eye'); } else { p.type = 'password'; i.classList.remove('fa-eye'); i.classList.add('fa-eye-slash'); }" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                            <i class="fas fa-eye-slash"></i>
                        </button>
                    </div>
                    @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="pt-2">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Assign Courses</label>
                    <div class="space-y-2 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
                        @foreach($courses as $course)
                        <label class="flex items-start gap-3 p-3 border border-gray-100 rounded-2xl cursor-pointer hover:bg-blue-50/50 hover:border-blue-100 transition group">
                            <input type="checkbox" name="courses[]" value="{{ $course->id }}" class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 shadow-sm">
                            <div>
                                <p class="text-sm font-semibold text-gray-800 group-hover:text-blue-700 transition">{{ $course->title }}</p>
                                <p class="text-[11px] text-gray-500 mt-0.5">{{ Str::limit($course->description, 40) }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('courses') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full mt-2 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-2xl transition shadow-[0_2px_10px_rgb(37,99,235,0.2)] flex items-center justify-center gap-2">
                    Register Student <i class="fas fa-arrow-right text-xs"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f8fafc; 
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1; 
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8; 
}
</style>
@endsection
