<?php

namespace App\Repositories\Banking;

use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankTransaction;
use App\Models\BankTransfer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BankingRepository
{
    public function dashboardStats(): array
    {
        return [
            'totalCashAcrossAllAccounts' => BankAccount::query()->where('is_active', true)->sum('current_balance'),
            'accountsNeedingReconciliation' => BankAccount::query()
                ->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('last_reconciled_date')->orWhere('last_reconciled_date', '<', now()->subDays(7)->toDateString()))
                ->count(),
            'unreconciledTransactions' => BankTransaction::query()->where('status', '!=', 'reconciled')->count(),
            'transactionsToday' => BankTransaction::query()->whereDate('transaction_date', now()->toDateString())->count(),
            'pendingTransfers' => BankTransfer::query()->where('status', 'pending')->count(),
        ];
    }

    public function recentTransactions(int $limit = 20): Collection
    {
        return BankTransaction::query()
            ->leftJoin('bank_accounts', 'bank_accounts.id', '=', 'bank_transactions.bank_account_id')
            ->orderByDesc('bank_transactions.transaction_date')
            ->limit($limit)
            ->get([
                'bank_transactions.*',
                'bank_accounts.name as account_name',
            ]);
    }

    public function accountsOverview(): Collection
    {
        return BankAccount::query()->orderBy('name')->get();
    }

    public function reconciliationAlerts(): Collection
    {
        return BankAccount::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('last_reconciled_date')->orWhere('last_reconciled_date', '<', now()->subDays(7)->toDateString()))
            ->orderBy('name')
            ->get();
    }

    public function transactionsPaginated(array $filters): LengthAwarePaginator
    {
        return BankTransaction::query()
            ->leftJoin('bank_accounts', 'bank_accounts.id', '=', 'bank_transactions.bank_account_id')
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('bank_transactions.status', $status))
            ->when($filters['account_id'] ?? null, fn ($q, $accountId) => $q->where('bank_transactions.bank_account_id', $accountId))
            ->orderByDesc('bank_transactions.transaction_date')
            ->select(['bank_transactions.*', 'bank_accounts.name as account_name'])
            ->paginate(20)
            ->withQueryString();
    }

    public function reconciliationsPaginated(): LengthAwarePaginator
    {
        return BankReconciliation::query()
            ->leftJoin('bank_accounts', 'bank_accounts.id', '=', 'bank_reconciliations.bank_account_id')
            ->orderByDesc('bank_reconciliations.statement_date')
            ->select(['bank_reconciliations.*', 'bank_accounts.name as account_name'])
            ->paginate(20);
    }

    public function transfersPaginated(): LengthAwarePaginator
    {
        return BankTransfer::query()
            ->leftJoin('bank_accounts as from_accounts', 'from_accounts.id', '=', 'bank_transfers.from_account_id')
            ->leftJoin('bank_accounts as to_accounts', 'to_accounts.id', '=', 'bank_transfers.to_account_id')
            ->orderByDesc('bank_transfers.transfer_date')
            ->select([
                'bank_transfers.*',
                'from_accounts.name as from_account_name',
                'to_accounts.name as to_account_name',
            ])
            ->paginate(20);
    }
}
