@extends('layouts.admin')

@section('header', 'Manage Courses')

@section('header_actions')
<a href="{{ route('admin.courses.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium shadow-[0_2px_10px_rgb(37,99,235,0.2)] hover:bg-blue-700 transition flex items-center gap-2 text-sm">
    <i class="fas fa-plus"></i> Add New Course
</a>
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 p-5 relative overflow-hidden group">
        <div class="flex items-center gap-3 relative z-10">
            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100/50">
                <i class="fas fa-book-open text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-gray-500 mb-0.5 tracking-wide uppercase">Total Courses</p>
                <p class="text-2xl font-bold text-gray-900 leading-none">{{ $courses->total() }}</p>
            </div>
        </div>
    </div>
</div>

<div class="w-full">
    <!-- List of Courses -->
    <div class="bg-white rounded-xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900">Course Master</h3>
        </div>
        
        <div class="p-0 overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-max">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-500 text-xs font-semibold uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4">Course Details</th>
                        <th class="px-6 py-4">Price</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($courses as $course)
                    <tr class="hover:bg-gray-50/80 transition group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-100 shrink-0">
                                    {{ substr($course->title, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 leading-tight mb-1">{{ $course->title }}</p>
                                    <p class="text-xs text-gray-500 line-clamp-1 max-w-sm">{{ $course->description }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2.5 py-1 rounded bg-[#e8f5e9] text-[#2e7d32] text-xs font-medium border border-[#c8e6c9]/50">
                                ${{ number_format($course->price, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.courses.lessons.index', $course->id) }}" class="w-7 h-7 rounded flex items-center justify-center text-gray-400 hover:text-green-600 hover:bg-green-50 transition" title="Manage Lessons">
                                    <i class="fas fa-book text-sm"></i>
                                </a>
                                <a href="{{ route('admin.courses.edit', $course->id) }}" class="w-7 h-7 rounded flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition" title="Edit Course">
                                    <i class="fas fa-pen text-sm"></i>
                                </a>
                                <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this course?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-7 h-7 rounded flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 transition" title="Delete Course">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-50 mb-3 text-gray-400">
                                <i class="fas fa-folder-open text-xl"></i>
                            </div>
                            <p class="text-gray-500 font-medium">No courses found. Start building your academy.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($courses->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
            {{ $courses->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
