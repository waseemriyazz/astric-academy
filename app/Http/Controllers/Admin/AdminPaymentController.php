<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentGatewayConfig;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminPaymentController extends Controller
{
    private const SORTABLE_COLUMNS = ['created_at', 'amount'];

    public function index(Request $request): View
    {
        $query = $this->applyFilters(Payment::with(['course', 'user'])->newQuery(), $request);

        // Whitelist sort columns — never interpolate the request value into orderBy directly.
        $sort = in_array($request->input('sort'), self::SORTABLE_COLUMNS, true) ? $request->input('sort') : 'created_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $statusCounts = (clone $query)->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')->pluck('count', 'status');

        // Revenue always means "paid" totals scoped by gateway/date/search — deliberately
        // built from a query that excludes the status filter, not from (clone $query). If it
        // reused $query, filtering the table to status=refunded would AND in `status = paid`
        // on top of the existing `status = refunded`, producing an impossible WHERE clause
        // and silently showing zero revenue instead of the real paid-revenue figure.
        $revenueQuery = $this->applyFilters(Payment::query(), $request, includeStatus: false)
            ->where('status', Payment::STATUS_PAID);
        $revenueByCurrency = $revenueQuery->select('currency', DB::raw('sum(amount) as total'))
            ->groupBy('currency')->pluck('total', 'currency');

        $payments = $query->orderBy($sort, $direction)->paginate(20)->withQueryString();

        $gateways = [PaymentGatewayConfig::GATEWAY_EASEBUZZ, PaymentGatewayConfig::GATEWAY_PAYGLOCAL];
        $statuses = [
            Payment::STATUS_PENDING,
            Payment::STATUS_PROCESSING,
            Payment::STATUS_PAID,
            Payment::STATUS_FAILED,
            Payment::STATUS_CANCELLED,
            Payment::STATUS_REFUNDED,
            Payment::STATUS_EXPIRED,
        ];

        return view('admin.payments.index', compact(
            'payments', 'statusCounts', 'revenueByCurrency', 'sort', 'direction', 'gateways', 'statuses'
        ));
    }

    public function show(Payment $payment): View
    {
        $payment->load(['course', 'user']);

        return view('admin.payments.show', compact('payment'));
    }

    private function applyFilters(Builder $query, Request $request, bool $includeStatus = true): Builder
    {
        return $query
            ->when($request->filled('gateway'), fn ($q) => $q->where('gateway', $request->input('gateway')))
            ->when($includeStatus && $request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('date_to')))
            ->when($request->filled('search'), function ($q) use ($request) {
                // Escape LIKE's own wildcards in user input (with an explicit ESCAPE clause,
                // required by SQLite and harmless on MySQL) — otherwise a search for
                // "john_doe@x.com" would also match "johnXdoe@x.com" via the unescaped "_".
                $term = '%' . addcslashes($request->input('search'), '%_\\') . '%';
                $q->where(function ($q) use ($term) {
                    $q->whereRaw("buyer_name LIKE ? ESCAPE '\\'", [$term])
                        ->orWhereRaw("buyer_email LIKE ? ESCAPE '\\'", [$term])
                        ->orWhereRaw("txnid LIKE ? ESCAPE '\\'", [$term])
                        ->orWhereRaw("payment_id LIKE ? ESCAPE '\\'", [$term]);
                });
            });
    }
}
