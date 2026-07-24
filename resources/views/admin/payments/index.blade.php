@extends('layouts.admin')

@section('header', 'Payments')

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

    $sortLink = fn (string $field) => request()->fullUrlWithQuery([
        'sort' => $field,
        'direction' => (request('sort') === $field && request('direction') === 'asc') ? 'desc' : 'asc',
        'page' => 1,
    ]);

    $sortIcon = function (string $field) {
        if (request('sort') !== $field) {
            return 'fa-sort text-gray-300';
        }
        return request('direction') === 'asc' ? 'fa-sort-up text-indigo-600' : 'fa-sort-down text-indigo-600';
    };
@endphp

<!-- Summary stats -->
<div class="flex flex-wrap gap-4 mb-6">
    @foreach($statusCounts as $status => $count)
        <div class="bg-white rounded-xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 px-5 py-4 min-w-[140px]">
            <p class="text-xs font-semibold uppercase tracking-wider {{ explode(' ', $statusColors[$status] ?? 'text-gray-500')[1] ?? 'text-gray-500' }}">{{ ucfirst($status) }}</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $count }}</p>
        </div>
    @endforeach

    @foreach($revenueByCurrency as $currency => $total)
        <div class="bg-white rounded-xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 px-5 py-4 min-w-[160px]">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Paid Revenue &middot; {{ $currency }}</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ config('currencies.symbols')[$currency] ?? $currency . ' ' }}{{ number_format($total, 2) }}</p>
        </div>
    @endforeach
</div>

<!-- Filter bar -->
<form method="GET" action="{{ route('admin.payments.index') }}" class="bg-white rounded-xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 p-4 mb-6">
    <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center">
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400 text-sm"></i>
            </div>
            <input type="text" name="search" value="{{ request('search') }}" class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 transition placeholder-gray-400" placeholder="Search buyer name, email, txnid...">
        </div>

        <select name="gateway" onchange="this.form.requestSubmit()" class="border border-gray-200 rounded-lg text-sm py-2 px-3 focus:ring-blue-500 focus:border-blue-500 text-gray-600 outline-none">
            <option value="">All Gateways</option>
            @foreach($gateways as $gateway)
                <option value="{{ $gateway }}" {{ request('gateway') === $gateway ? 'selected' : '' }}>{{ ucfirst($gateway) }}</option>
            @endforeach
        </select>

        <select name="status" onchange="this.form.requestSubmit()" class="border border-gray-200 rounded-lg text-sm py-2 px-3 focus:ring-blue-500 focus:border-blue-500 text-gray-600 outline-none">
            <option value="">All Status</option>
            @foreach($statuses as $status)
                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
            @endforeach
        </select>

        <div class="flex items-center gap-2">
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-gray-200 rounded-lg text-sm py-2 px-3 focus:ring-blue-500 focus:border-blue-500 text-gray-600 outline-none">
            <span class="text-gray-400 text-sm">to</span>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-gray-200 rounded-lg text-sm py-2 px-3 focus:ring-blue-500 focus:border-blue-500 text-gray-600 outline-none">
        </div>

        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition flex items-center gap-2 text-sm whitespace-nowrap">
            <i class="fas fa-filter"></i> Filter
        </button>

        @if(request()->anyFilled(['search', 'gateway', 'status', 'date_from', 'date_to']))
            <a href="{{ route('admin.payments.index') }}" class="px-4 py-2 border border-gray-200 text-gray-500 rounded-lg font-medium hover:bg-gray-50 transition text-sm whitespace-nowrap">
                Clear
            </a>
        @endif
    </div>
    <p class="text-xs text-gray-400 mt-3">Dates shown in UTC.</p>
</form>

<!-- Payments table -->
<div class="bg-white rounded-xl shadow-[0_2px_10px_rgb(0,0,0,0.02)] border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-max">
            <thead>
                <tr class="bg-gray-50/50 text-gray-500 text-xs font-semibold uppercase tracking-wider border-b border-gray-100">
                    <th class="px-6 py-4">
                        <a href="{{ $sortLink('created_at') }}" class="flex items-center gap-1.5 hover:text-gray-700">
                            Date <i class="fas {{ $sortIcon('created_at') }} text-[10px]"></i>
                        </a>
                    </th>
                    <th class="px-6 py-4">Buyer</th>
                    <th class="px-6 py-4">Course</th>
                    <th class="px-6 py-4">Gateway</th>
                    <th class="px-6 py-4">
                        <a href="{{ $sortLink('amount') }}" class="flex items-center gap-1.5 hover:text-gray-700">
                            Amount <i class="fas {{ $sortIcon('amount') }} text-[10px]"></i>
                        </a>
                    </th>
                    <th class="px-6 py-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @forelse($payments as $payment)
                <tr class="hover:bg-gray-50/50 transition cursor-pointer" onclick="window.location='{{ route('admin.payments.show', $payment) }}'">
                    <td class="px-6 py-4">
                        <p class="text-gray-900 font-medium">{{ $payment->created_at->format('d M Y') }}</p>
                        <p class="text-gray-500 text-xs mt-0.5">{{ $payment->created_at->format('h:i A') }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-semibold text-gray-900">{{ $payment->buyer_name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $payment->buyer_email }}</p>
                    </td>
                    <td class="px-6 py-4 text-gray-700">{{ $payment->course?->title ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                            <i class="fas {{ $payment->gateway === 'payglocal' ? 'fa-globe' : 'fa-bolt' }} text-[10px]"></i>
                            {{ ucfirst($payment->gateway) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-900 font-medium">
                        {{ $payment->currencySymbol() }}{{ number_format($payment->amount, 2) }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $statusColors[$payment->status] ?? 'bg-gray-50 text-gray-500 border-gray-200' }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-50 mb-3 text-gray-400">
                            <i class="fas fa-receipt text-xl"></i>
                        </div>
                        <p class="text-gray-500 font-medium">No payments found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
        {{ $payments->links() }}
    </div>
    @endif
</div>
@endsection
