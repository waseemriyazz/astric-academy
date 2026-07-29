@extends('layouts.admin')

@section('header', 'Payment Details')

@section('header_actions')
<a href="{{ route('admin.payments.index') }}" class="px-4 py-2 border border-gray-200 text-gray-600 rounded-2xl font-medium hover:bg-gray-50 transition flex items-center gap-2 text-sm">
    <i class="fas fa-arrow-left"></i> Back to Payments
</a>
@endsection

@section('content')
@php
    $statusColors = [
        'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'processing' => 'bg-blue-50 text-blue-700 border-blue-200',
        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
        'failed' => 'bg-red-50 text-red-700 border-red-200',
        'cancelled' => 'bg-gray-50 text-gray-500 border-gray-200',
        'expired' => 'bg-gray-50 text-gray-500 border-gray-200',
        'refunded' => 'bg-purple-50 text-purple-700 border-purple-200',
    ];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Transaction ID</p>
                    <p class="text-lg font-bold text-gray-900 font-mono">{{ $payment->txnid }}</p>
                </div>
                <span class="px-3 py-1.5 rounded-full text-sm font-semibold border {{ $statusColors[$payment->status] ?? 'bg-gray-50 text-gray-500 border-gray-200' }}">
                    {{ ucfirst($payment->status) }}
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-6">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Amount</p>
                    <p class="text-gray-900 font-semibold">{{ $payment->currencySymbol() }}{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Gateway</p>
                    <p class="text-gray-900 font-semibold flex items-center gap-1.5">
                        <i class="fas {{ $payment->gateway === 'payglocal' ? 'fa-globe' : 'fa-bolt' }} text-brand-500 text-xs"></i>
                        {{ ucfirst($payment->gateway) }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Gateway Payment ID</p>
                    <p class="text-gray-900 font-mono text-sm">{{ $payment->payment_id ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Created</p>
                    <p class="text-gray-900">{{ $payment->created_at->format('d M Y, h:i A') }} <span class="text-xs text-gray-400">UTC</span></p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Last Updated</p>
                    <p class="text-gray-900">{{ $payment->updated_at->format('d M Y, h:i A') }} <span class="text-xs text-gray-400">UTC</span></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 p-6">
            <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-code text-gray-400"></i> Raw Gateway Response
            </h3>
            <pre class="bg-gray-900 text-gray-100 text-xs rounded-2xl p-4 overflow-x-auto max-h-[420px] overflow-y-auto">{{ json_encode($payment->gateway_response, JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 p-6">
            <h3 class="text-sm font-bold text-gray-900 mb-4">Buyer</h3>
            <div class="space-y-3 text-sm">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Name</p>
                    <p class="text-gray-900 font-medium">{{ $payment->buyer_name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Email</p>
                    <p class="text-gray-900">{{ $payment->buyer_email }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Phone</p>
                    <p class="text-gray-900">{{ $payment->buyer_phone }}</p>
                </div>
                @if($payment->user)
                <div class="pt-2 border-t border-gray-100">
                    <a href="{{ route('admin.users.edit', $payment->user) }}" class="text-brand-600 hover:text-brand-700 text-sm font-medium flex items-center gap-1.5">
                        <i class="fas fa-user"></i> View student account
                    </a>
                </div>
                @else
                <p class="text-xs text-gray-400 italic pt-2 border-t border-gray-100">Guest checkout — no registered account linked.</p>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 p-6">
            <h3 class="text-sm font-bold text-gray-900 mb-4">Course</h3>
            @if($payment->course)
                <p class="text-gray-900 font-medium mb-1">{{ $payment->course->title }}</p>
                <a href="{{ route('admin.courses.edit', $payment->course) }}" class="text-brand-600 hover:text-brand-700 text-sm font-medium flex items-center gap-1.5 mt-2">
                    <i class="fas fa-book"></i> View course
                </a>
            @else
                <p class="text-gray-400 italic text-sm">Course no longer exists.</p>
            @endif
        </div>
    </div>
</div>
@endsection
