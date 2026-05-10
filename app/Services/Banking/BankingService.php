<?php

namespace App\Services\Banking;

use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankRule;
use App\Models\BankTransaction;
use App\Models\BankTransfer;
use App\Models\ChangeLog;
use App\Repositories\Banking\BankingRepository;
use Illuminate\Support\Facades\DB;

class BankingService
{
    public function __construct(private readonly BankingRepository $repository)
    {
    }

    public function dashboardPayload(): array
    {
        return [
            'stats' => $this->repository->dashboardStats(),
            'recentTransactions' => $this->repository->recentTransactions(),
            'accountsOverview' => $this->repository->accountsOverview(),
            'reconciliationAlerts' => $this->repository->reconciliationAlerts(),
            'quickLinks' => [
                ['label' => 'Record Manual Transaction', 'href' => '/banking/transactions/create'],
                ['label' => 'Create Bank Transfer', 'href' => '/banking/transfers/create'],
                ['label' => 'Import Bank Statement', 'href' => '/banking/reconciliations/create'],
                ['label' => 'View All Transactions', 'href' => '/banking/transactions'],
                ['label' => 'Reconcile Accounts', 'href' => '/banking/reconciliations'],
                ['label' => 'Manage Bank Rules', 'href' => '/admin/bank-rules'],
            ],
        ];
    }

    public function createAccount(array $data, ?string $userId): BankAccount
    {
        $account = BankAccount::query()->create([
            ...$data,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        $this->log('bank_accounts', $account->id, 'create', [], $account->toArray(), $userId);

        return $account;
    }

    public function createTransaction(array $data, ?string $userId): BankTransaction
    {
        return DB::transaction(function () use ($data, $userId) {
            $transaction = BankTransaction::query()->create([
                ...$data,
                'status' => $data['status'] ?? 'pending',
                'category_source' => $data['category_source'] ?? 'manual',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $this->applyBalanceImpact($transaction->bank_account_id, (float) $transaction->amount, $transaction->transaction_type);
            $this->tryApplyRule($transaction, $userId);
            $this->log('bank_transactions', $transaction->id, 'create', [], $transaction->fresh()->toArray(), $userId);

            return $transaction->fresh();
        });
    }

    public function updateTransaction(BankTransaction $transaction, array $data, ?string $userId): BankTransaction
    {
        $old = $transaction->toArray();

        $transaction->update([
            ...$data,
            'updated_by' => $userId,
        ]);

        $this->log('bank_transactions', $transaction->id, 'update', $old, $transaction->fresh()->toArray(), $userId);

        return $transaction->fresh();
    }

    public function categorizeTransaction(BankTransaction $transaction, array $data, ?string $userId): BankTransaction
    {
        $old = $transaction->toArray();

        $transaction->update([
            'internal_category' => $data['internal_category'],
            'category_source' => $data['source'] ?? 'manual',
            'status' => $transaction->status === 'reconciled' ? 'reconciled' : 'categorized',
            'updated_by' => $userId,
        ]);

        $this->log('bank_transactions', $transaction->id, 'automation', $old, $transaction->fresh()->toArray(), $userId, [
            'source' => 'form automation',
            'rule' => 'categorize transaction',
        ]);

        return $transaction->fresh();
    }

    public function linkJournalEntry(BankTransaction $transaction, array $data, ?string $userId): BankTransaction
    {
        $old = $transaction->toArray();

        $transaction->update([
            'journal_entry_id' => $data['journal_entry_id'],
            'updated_by' => $userId,
        ]);

        $this->log('bank_transactions', $transaction->id, 'automation', $old, $transaction->fresh()->toArray(), $userId, [
            'source' => 'form automation',
            'rule' => 'link transaction to journal entry',
        ]);

        return $transaction->fresh();
    }

    public function deleteManualTransaction(BankTransaction $transaction, ?string $userId): void
    {
        if (($transaction->category_source ?? '') !== 'manual') {
            throw new \RuntimeException('Only manual transactions can be deleted.');
        }

        $old = $transaction->toArray();
        $transaction->delete();
        $this->log('bank_transactions', $old['id'], 'delete', $old, [], $userId);
    }

    public function createReconciliation(array $data, ?string $userId): BankReconciliation
    {
        $reconciliation = BankReconciliation::query()->create([
            ...$data,
            'status' => $data['status'] ?? 'pending',
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        $this->recalculateReconciliation($reconciliation, $userId);
        $this->log('bank_reconciliations', $reconciliation->id, 'create', [], $reconciliation->fresh()->toArray(), $userId);

        return $reconciliation->fresh();
    }

    public function matchTransaction(BankReconciliation $reconciliation, BankTransaction $transaction, ?string $userId): void
    {
        DB::transaction(function () use ($reconciliation, $transaction, $userId) {
            $old = $transaction->toArray();

            $transaction->update([
                'status' => 'reconciled',
                'updated_by' => $userId,
            ]);

            $this->log('bank_transactions', $transaction->id, 'automation', $old, $transaction->fresh()->toArray(), $userId, [
                'source' => 'form automation',
                'rule' => 'matched during reconciliation',
                'reconciliation_id' => $reconciliation->id,
            ]);

            $this->recalculateReconciliation($reconciliation, $userId);
        });
    }

    public function createAdjustment(BankReconciliation $reconciliation, array $data, ?string $userId): BankTransaction
    {
        return DB::transaction(function () use ($reconciliation, $data, $userId) {
            $difference = (float) $reconciliation->difference;
            $transaction = BankTransaction::query()->create([
                'bank_account_id' => $reconciliation->bank_account_id,
                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
                'amount' => abs($difference) > 0 ? abs($difference) : (float) ($data['amount'] ?? 0),
                'transaction_type' => $difference >= 0 ? 'deposit' : 'withdrawal',
                'description' => $data['description'] ?? 'Reconciliation adjustment',
                'internal_category' => $data['internal_category'] ?? 'reconciliation_adjustment',
                'category_source' => 'manual',
                'status' => 'reconciled',
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $this->applyBalanceImpact($transaction->bank_account_id, (float) $transaction->amount, $transaction->transaction_type);
            $this->recalculateReconciliation($reconciliation, $userId);

            return $transaction;
        });
    }

    public function completeReconciliation(BankReconciliation $reconciliation, ?string $userId): BankReconciliation
    {
        $this->recalculateReconciliation($reconciliation, $userId);

        if ((float) $reconciliation->fresh()->difference !== 0.0) {
            throw new \RuntimeException('Reconciliation cannot be completed until difference is zero.');
        }

        $reconciliation->update([
            'status' => 'reconciled',
            'completed_at' => now(),
            'updated_by' => $userId,
        ]);

        BankAccount::query()->whereKey($reconciliation->bank_account_id)->update([
            'last_reconciled_date' => $reconciliation->statement_date,
            'updated_by' => $userId,
        ]);

        $this->log('bank_reconciliations', $reconciliation->id, 'automation', [], $reconciliation->fresh()->toArray(), $userId, [
            'source' => 'form automation',
            'rule' => 'complete reconciliation when difference is zero',
        ]);

        return $reconciliation->fresh();
    }

    public function createTransfer(array $data, ?string $userId): BankTransfer
    {
        return DB::transaction(function () use ($data, $userId) {
            $transfer = BankTransfer::query()->create([
                'transfer_number' => $data['transfer_number'] ?? $this->nextTransferNumber(),
                'from_account_id' => $data['from_account_id'],
                'to_account_id' => $data['to_account_id'],
                'amount' => $data['amount'],
                'transfer_date' => $data['transfer_date'],
                'status' => $data['status'] ?? 'completed',
                'description' => $data['description'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $this->applyBalanceImpact($transfer->from_account_id, (float) $transfer->amount, 'transfer_out');
            $this->applyBalanceImpact($transfer->to_account_id, (float) $transfer->amount, 'transfer_in');

            $this->log('bank_transfers', $transfer->id, 'create', [], $transfer->toArray(), $userId);

            return $transfer;
        });
    }

    public function recalculateReconciliation(BankReconciliation $reconciliation, ?string $userId): void
    {
        $bookBalance = (float) BankTransaction::query()
            ->where('bank_account_id', $reconciliation->bank_account_id)
            ->where('status', 'reconciled')
            ->whereDate('transaction_date', '<=', $reconciliation->statement_date)
            ->sum('amount');

        $difference = round((float) $reconciliation->statement_balance - $bookBalance, 2);

        $unreconciledCount = BankTransaction::query()
            ->where('bank_account_id', $reconciliation->bank_account_id)
            ->whereDate('transaction_date', '<=', $reconciliation->statement_date)
            ->where('status', '!=', 'reconciled')
            ->count();

        $status = $difference === 0.0 && $unreconciledCount === 0 ? 'reconciled' : ($difference === 0.0 ? 'pending' : 'discrepancies');

        $reconciliation->update([
            'book_balance' => $bookBalance,
            'difference' => $difference,
            'status' => $status,
            'updated_by' => $userId,
        ]);
    }

    private function tryApplyRule(BankTransaction $transaction, ?string $userId): void
    {
        $rule = BankRule::query()->where('is_active', true)->orderBy('priority')->first();
        if (! $rule) {
            return;
        }

        $category = $rule->action['set_internal_category'] ?? null;
        if (! $category) {
            return;
        }

        $old = $transaction->toArray();
        $transaction->update([
            'internal_category' => $category,
            'category_source' => 'rule',
            'status' => 'categorized',
            'updated_by' => $userId,
        ]);

        $this->log('bank_transactions', $transaction->id, 'automation', $old, $transaction->fresh()->toArray(), $userId, [
            'source' => 'form automation',
            'rule' => 'bank rule applied',
            'bank_rule_id' => $rule->id,
        ]);
    }

    private function applyBalanceImpact(string $accountId, float $amount, string $type): void
    {
        $signed = in_array($type, ['withdrawal', 'transfer_out', 'fee'], true) ? -1 * $amount : $amount;
        BankAccount::query()->whereKey($accountId)->increment('current_balance', $signed);
    }

    private function log(string $table, string $recordId, string $action, array $old, array $new, ?string $userId, array $metadata = []): void
    {
        ChangeLog::query()->create([
            'table_name' => $table,
            'record_id' => $recordId,
            'action' => $action,
            'user_id' => $userId,
            'changes' => ['old' => $old, 'new' => $new],
            'metadata' => $metadata,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    private function nextTransferNumber(): string
    {
        $last = BankTransfer::query()->orderByDesc('created_at')->value('transfer_number');
        $num = 1;
        if ($last && preg_match('/TRF-(\d+)/', $last, $m)) {
            $num = ((int) $m[1]) + 1;
        }

        return sprintf('TRF-%04d', $num);
    }
}
