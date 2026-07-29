@extends('layouts.admin')

@section('header', 'Enrolled Courses')

@section('header_actions')
<a href="{{ route('admin.enrollments.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-2xl font-medium shadow-[0_2px_10px_rgb(37,99,235,0.2)] hover:bg-blue-700 transition flex items-center gap-2 text-sm">
    <i class="fas fa-plus"></i> Add Enrollment
</a>
@endsection

@section('content')
<!-- Stats Overview -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-2xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 p-5 relative overflow-hidden group">
        <div class="flex items-center gap-3 relative z-10">
            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100/50">
                <i class="fas fa-layer-group text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-gray-500 mb-0.5 tracking-wide uppercase">Total Enrollments</p>
                <p class="text-2xl font-bold text-gray-900 leading-none">{{ $totalEnrollments }}</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 p-5 relative overflow-hidden group">
        <div class="flex items-center gap-3 relative z-10">
            <div class="w-10 h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center shrink-0 border border-green-100/50">
                <i class="fas fa-check-circle text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-gray-500 mb-0.5 tracking-wide uppercase">Active</p>
                <p class="text-2xl font-bold text-gray-900 leading-none">{{ $activeEnrollments }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 p-5 relative overflow-hidden group">
        <div class="flex items-center gap-3 relative z-10">
            <div class="w-10 h-10 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 border border-purple-100/50">
                <i class="fas fa-award text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-gray-500 mb-0.5 tracking-wide uppercase">Completed</p>
                <p class="text-2xl font-bold text-gray-900 leading-none">{{ $completed }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 p-5 relative overflow-hidden group">
        <div class="flex items-center gap-3 relative z-10">
            <div class="w-10 h-10 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center shrink-0 border border-orange-100/50">
                <i class="fas fa-ban text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-gray-500 mb-0.5 tracking-wide uppercase">Cancelled</p>
                <p class="text-2xl font-bold text-gray-900 leading-none">{{ $cancelled }}</p>
            </div>
        </div>
    </div>
</div>

<div class="w-full">
    <!-- Filters and Table -->
    <div class="bg-white rounded-2xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 overflow-hidden">
        
        <div class="p-6 border-b border-gray-100 bg-gray-50/30 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="relative w-full md:w-96">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" class="w-full pl-10 pr-4 py-2 rounded-2xl border-gray-200 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm" placeholder="Search by student name or course...">
            </div>
            
            <div class="flex gap-3">
                <select class="rounded-2xl border-gray-200 text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm py-2 px-3 pr-8">
                    <option>All Courses</option>
                </select>
                <select class="rounded-2xl border-gray-200 text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm py-2 px-3 pr-8">
                    <option>Status: All</option>
                </select>
            </div>
        </div>
        
        <div class="p-0 overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-max">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-500 text-xs font-semibold uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4">Student</th>
                        <th class="px-6 py-4">Course Enrolled</th>
                        <th class="px-6 py-4">Enrollment Date</th>
                        <th class="px-6 py-4">Status & Payment</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($students as $student)
                        @foreach($student->courses as $course)
                        <tr class="hover:bg-gray-50/80 transition group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-100 shrink-0">
                                        {{ substr($student->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 leading-tight mb-1">{{ $student->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $student->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-800 leading-tight mb-1">{{ $course->title }}</p>
                                @if($course->tools_count > 0)
                                    <p class="text-[11px] text-blue-600 font-medium">+ {{ $course->tools_count }} Tools Program</p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900 leading-tight mb-1">{{ \Carbon\Carbon::parse($course->pivot->created_at)->format('d M Y') }}</p>
                                <p class="text-[11px] text-gray-500 uppercase tracking-wider">{{ \Carbon\Carbon::parse($course->pivot->created_at)->diffForHumans() }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1.5 items-start">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-medium bg-[#e8f5e9] text-[#2e7d32] border border-[#c8e6c9]/50 uppercase tracking-wider">
                                        Active
                                    </span>
                                    <span class="text-xs text-gray-500 font-medium">
                                        Paid
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <form action="{{ route('admin.enrollments.destroy', [$student->id, $course->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to revoke this enrollment? The student will lose access immediately.');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-7 h-7 rounded flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 transition" title="Revoke Enrollment">
                                            <i class="fas fa-trash-alt text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-50 mb-3 text-gray-400">
                                <i class="fas fa-users-slash text-xl"></i>
                            </div>
                            <p class="text-gray-500 font-medium">No enrollments found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30 flex justify-between items-center text-sm text-gray-500">
            @if($students->hasPages())
                {{ $students->links() }}
            @else
                <span>Showing recent enrollments</span>
            @endif
        </div>
    </div>
</div>
@endsection
