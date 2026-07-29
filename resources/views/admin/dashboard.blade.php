@extends('layouts.admin')

@section('header', 'Dashboard Overview')

@section('content')
<!-- Stats Overview -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-2xl shadow-card border border-ink-100 p-5 relative overflow-hidden group">
        <div class="flex items-center gap-3 relative z-10">
            <div class="w-10 h-10 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center shrink-0 border border-brand-100/50">
                <i class="fas fa-users text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-gray-500 mb-0.5 tracking-wide uppercase">Total Students</p>
                <p class="text-2xl font-bold text-gray-900 leading-none">{{ $totalStudents }}</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl shadow-card border border-ink-100 p-5 relative overflow-hidden group">
        <div class="flex items-center gap-3 relative z-10">
            <div class="w-10 h-10 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 border border-purple-100/50">
                <i class="fas fa-book-open text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-gray-500 mb-0.5 tracking-wide uppercase">Total Courses</p>
                <p class="text-2xl font-bold text-gray-900 leading-none">{{ $totalCourses }}</p>
            </div>
        </div>
    </div>

    <!-- Placeholder Cards -->
    <div class="bg-white rounded-2xl shadow-card border border-ink-100 p-5 relative overflow-hidden group">
        <div class="flex items-center gap-3 relative z-10">
            <div class="w-10 h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center shrink-0 border border-green-100/50">
                <i class="fas fa-wallet text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-gray-500 mb-0.5 tracking-wide uppercase">Total Revenue</p>
                <p class="text-2xl font-bold text-gray-900 leading-none">$12,450</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-ink-100 p-5 relative overflow-hidden group">
        <div class="flex items-center gap-3 relative z-10">
            <div class="w-10 h-10 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center shrink-0 border border-orange-100/50">
                <i class="fas fa-chart-line text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-gray-500 mb-0.5 tracking-wide uppercase">Active Sessions</p>
                <p class="text-2xl font-bold text-gray-900 leading-none">84</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Main Table Area -->
    <div class="lg:col-span-2 space-y-8">
        <div class="bg-white rounded-2xl shadow-card border border-ink-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-ink-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-900">Recently Registered Students</h3>
                <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">View All &rarr;</a>
            </div>
            <div class="p-0 overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-max">
                    <thead>
                        <tr class="bg-gray-50/50 text-gray-500 text-xs font-semibold uppercase tracking-wider border-b border-ink-100">
                            <th class="px-6 py-4">Student Info</th>
                            <th class="px-6 py-4">Registered Date</th>
                            <th class="px-6 py-4 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @forelse($recentStudents as $student)
                        <tr class="hover:bg-gray-50/80 transition group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-sm border border-brand-100 shrink-0">
                                        {{ substr($student->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 leading-tight mb-1">{{ $student->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $student->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900 leading-tight mb-1">{{ $student->created_at->format('d M Y') }}</p>
                                <p class="text-[11px] text-gray-500 uppercase tracking-wider">{{ $student->created_at->format('h:i A') }}</p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="inline-flex px-2.5 py-1 rounded bg-[#e8f5e9] text-[#2e7d32] text-xs font-medium border border-[#c8e6c9]/50">
                                    Active
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-50 mb-3 text-gray-400">
                                    <i class="fas fa-user-slash text-xl"></i>
                                </div>
                                <p class="text-gray-500 font-medium">No students registered yet.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Sidebar Area (Activity/Quick Actions) -->
    <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl shadow-card border border-ink-100 p-6">
            <h3 class="text-base font-bold text-gray-900 mb-5">Quick Actions</h3>
            <div class="space-y-3">
                <a href="{{ route('admin.courses.index') }}" class="flex items-center gap-4 p-3 rounded-2xl border border-ink-100 hover:border-brand-200 hover:bg-brand-50/50 transition group cursor-pointer">
                    <div class="w-10 h-10 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center group-hover:bg-brand-600 group-hover:text-white transition shadow-sm border border-brand-100">
                        <i class="fas fa-plus text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800 group-hover:text-brand-700 transition">Add New Course</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">Create a new learning program</p>
                    </div>
                </a>
                
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-4 p-3 rounded-2xl border border-ink-100 hover:border-purple-200 hover:bg-purple-50/50 transition group cursor-pointer">
                    <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition shadow-sm border border-purple-100">
                        <i class="fas fa-user-plus text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800 group-hover:text-purple-700 transition">Register Student</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">Manually add a student</p>
                    </div>
                </a>
                
                <a href="#" class="flex items-center gap-4 p-3 rounded-2xl border border-ink-100 hover:border-orange-200 hover:bg-orange-50/50 transition group cursor-pointer">
                    <div class="w-10 h-10 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center group-hover:bg-orange-600 group-hover:text-white transition shadow-sm border border-orange-100">
                        <i class="fas fa-bullhorn text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800 group-hover:text-orange-700 transition">Post Announcement</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">Notify all enrolled students</p>
                    </div>
                </a>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="bg-white rounded-2xl shadow-card border border-ink-100 p-6">
            <h3 class="text-base font-bold text-gray-900 mb-5">Recent Activity</h3>
            <div class="relative pl-4 border-l-2 border-ink-100 space-y-6 ml-2">
                <div class="relative">
                    <div class="absolute -left-[23px] top-1 w-3 h-3 rounded-full bg-brand-500 border-2 border-white box-content shadow-sm"></div>
                    <p class="text-sm font-semibold text-gray-800 leading-tight mb-1">New enrollment</p>
                    <p class="text-[11px] text-gray-500">Jane Doe enrolled in Web Dev Bootcamp.</p>
                    <p class="text-[10px] text-gray-400 font-semibold mt-1 uppercase tracking-wider">2 hrs ago</p>
                </div>
                <div class="relative">
                    <div class="absolute -left-[23px] top-1 w-3 h-3 rounded-full bg-green-500 border-2 border-white box-content shadow-sm"></div>
                    <p class="text-sm font-semibold text-gray-800 leading-tight mb-1">Course completed</p>
                    <p class="text-[11px] text-gray-500">Mark Smith finished UI/UX Masterclass.</p>
                    <p class="text-[10px] text-gray-400 font-semibold mt-1 uppercase tracking-wider">5 hrs ago</p>
                </div>
                <div class="relative">
                    <div class="absolute -left-[23px] top-1 w-3 h-3 rounded-full bg-orange-500 border-2 border-white box-content shadow-sm"></div>
                    <p class="text-sm font-semibold text-gray-800 leading-tight mb-1">System Update</p>
                    <p class="text-[11px] text-gray-500">Platform maintenance completed successfully.</p>
                    <p class="text-[10px] text-gray-400 font-semibold mt-1 uppercase tracking-wider">1 day ago</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
