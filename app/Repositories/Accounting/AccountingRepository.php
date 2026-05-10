<?php

namespace App\Repositories\Accounting;

use App\Models\BankAccount;
use App\Models\Bill;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AccountingRepository
{
    public function dashboardStats(): array
    {
        return [
            'totalOutstandingReceivables' => Invoice::query()->whereIn('status', ['sent', 'partial', 'overdue'])->sum('amount_due'),
            'overdueInvoices' => Invoice::query()->where('status', 'overdue')->count(),
            'invoicesDueThisWeek' => Invoice::query()->whereBetween('due_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()])->count(),
            'totalOutstandingPayables' => Bill::query()->whereIn('status', ['received', 'approved', 'overdue'])->sum('total_amount'),
            'billsDueThisWeek' => Bill::query()->whereBetween('due_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()])->count(),
            'cashOnHand' => BankAccount::query()->where('is_active', true)->sum('current_balance'),
            'revenueThisMonth' => Payment::query()->where('payment_direction', 'incoming')->whereMonth('payment_date', now()->month)->sum('amount'),
            'expensesThisMonth' => Payment::query()->where('payment_direction', 'outgoing')->whereMonth('payment_date', now()->month)->sum('amount'),
        ];
    }

    public function recentInvoices(int $limit = 10): Collection
    {
        return Invoice::query()->latest()->limit($limit)->get();
    }

    public function recentBills(int $limit = 10): Collection
    {
        return Bill::query()->latest()->limit($limit)->get();
    }

    public function pendingPayments(int $limit = 10): Collection
    {
        return Payment::query()->where('reconciliation_status', 'pending')->latest('payment_date')->limit($limit)->get();
    }

    public function invoicesPaginated(array $filters): LengthAwarePaginator
    {
        return Invoice::query()
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();
    }

    public function billsPaginated(array $filters): LengthAwarePaginator
    {
        return Bill::query()
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();
    }

    public function paymentsPaginated(): LengthAwarePaginator
    {
        return Payment::query()->orderByDesc('payment_date')->paginate(20);
    }

    public function journalEntriesPaginated(): LengthAwarePaginator
    {
        return JournalEntry::query()->orderByDesc('entry_date')->paginate(20);
    }
}
