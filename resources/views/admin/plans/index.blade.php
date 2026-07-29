@extends('layouts.admin')

@section('header', 'Course Plans')

@section('header_actions')
<a href="{{ route('admin.plans.create', ['course_id' => request('course_id')]) }}" class="px-4 py-2 bg-blue-600 text-white rounded-2xl font-medium shadow-[0_2px_10px_rgb(37,99,235,0.2)] hover:bg-blue-700 transition flex items-center gap-2 text-sm">
    <i class="fas fa-plus"></i> Add New Plan
</a>
@endsection

@section('content')
<!-- Filter by Course -->
<div class="bg-white rounded-2xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 p-5 mb-6">
    <form method="GET" action="{{ route('admin.plans.index') }}" class="flex items-center gap-4">
        <label for="course_id" class="text-sm font-semibold text-gray-700">Filter by Course:</label>
        <select name="course_id" id="course_id" onchange="this.form.submit()" class="border border-gray-200 rounded-2xl px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Courses</option>
            @foreach($courses as $course)
                <option value="{{ $course->id }}" {{ (string)$courseId === (string)$course->id ? 'selected' : '' }}>
                    {{ $course->title }}
                </option>
            @endforeach
        </select>
        @if($courseId)
            <a href="{{ route('admin.plans.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i> Clear
            </a>
        @endif
    </form>
</div>

<div class="w-full">
    <div class="bg-white rounded-2xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900">Plan Master</h3>
            <span class="text-sm text-gray-500">{{ $plans->total() }} plan(s)</span>
        </div>
        
        <div class="p-0 overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-max">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-500 text-xs font-semibold uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4">Course</th>
                        <th class="px-6 py-4">Tier Name</th>
                        <th class="px-6 py-4">Price</th>
                        <th class="px-6 py-4">Duration</th>
                        <th class="px-6 py-4">Sort Order</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($plans as $plan)
                    <tr class="hover:bg-gray-50/80 transition group">
                        <td class="px-6 py-4">
                            <span class="font-medium text-gray-900">{{ $plan->course->title }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-gray-900">{{ $plan->tier_name }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2.5 py-1 rounded bg-[#e8f5e9] text-[#2e7d32] text-xs font-medium border border-[#c8e6c9]/50">
                                ${{ number_format($plan->price, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-gray-700">{{ $plan->duration ?? '—' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-gray-700">{{ $plan->sort_order }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($plan->is_active)
                                <span class="inline-flex px-2.5 py-1 rounded bg-green-50 text-green-700 text-xs font-medium border border-green-100/50">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex px-2.5 py-1 rounded bg-red-50 text-red-700 text-xs font-medium border border-red-100/50">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.plans.edit', $plan->id) }}" class="w-7 h-7 rounded flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition" title="Edit Plan">
                                    <i class="fas fa-pen text-sm"></i>
                                </a>
                                <form action="{{ route('admin.plans.destroy', $plan->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this plan?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-7 h-7 rounded flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 transition" title="Delete Plan">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-50 mb-3 text-gray-400">
                                <i class="fas fa-layer-group text-xl"></i>
                            </div>
                            <p class="text-gray-500 font-medium">No plans found for this course.</p>
                            <a href="{{ route('admin.plans.create', ['course_id' => $courseId]) }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium mt-2 inline-block">
                                Create the first plan
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($plans->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
            {{ $plans->links() }}
        </div>
        @endif
    </div>
</div>
@endsection