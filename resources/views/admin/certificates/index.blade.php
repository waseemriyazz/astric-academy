@extends('layouts.admin')

@section('header', 'Certificates Management')

@section('content')
<div class="space-y-6">
    <!-- Issue Certificate Form -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <i class="fas fa-award text-indigo-600"></i> Issue New Certificate
        </h3>
        <form action="{{ route('admin.certificates.issue') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <div class="flex-1">
                <select name="user_id" required class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select Student</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1">
                <select name="course_id" required class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Select Course</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->title }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition shadow-sm shrink-0">
                <i class="fas fa-plus"></i> Issue Certificate
            </button>
        </form>
    </div>

    <!-- Certificates Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-900">All Certificates</h3>
            <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-3 py-1.5 rounded-full">
                {{ $certificates->total() }} Total
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4">Student</th>
                        <th class="px-6 py-4">Course</th>
                        <th class="px-6 py-4">Serial Number</th>
                        <th class="px-6 py-4">Issued Date</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($certificates as $cert)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs">
                                        {{ substr($cert->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $cert->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $cert->user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900">{{ $cert->course->title }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-mono font-semibold text-gray-700">{{ $cert->serial_number }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600">{{ $cert->issued_at ? $cert->issued_at->format('M d, Y') : 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($cert->is_revoked)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">
                                        <i class="fas fa-ban"></i> Revoked
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
                                        <i class="fas fa-check-circle"></i> Active
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.certificates.download', $cert->id) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                                        <i class="fas fa-download"></i> PDF
                                    </a>
                                    @if(!$cert->is_revoked)
                                        <form action="{{ route('admin.certificates.revoke', $cert->id) }}" method="POST" onsubmit="return confirm('Revoke this certificate?')">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 rounded-lg transition">
                                                <i class="fas fa-ban"></i> Revoke
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-certificate text-2xl text-gray-300"></i>
                                </div>
                                <p class="text-sm font-medium text-gray-500">No certificates issued yet.</p>
                                <p class="text-xs text-gray-400 mt-1">Use the form above to issue your first certificate.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($certificates->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $certificates->links() }}
            </div>
        @endif
    </div>
</div>
@endsection