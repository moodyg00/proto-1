<?php

namespace App\Services\Accounting;

use App\Models\Bill;
use App\Models\ChangeLog;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Payment;
use App\Repositories\Accounting\AccountingRepository;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    public function __construct(private readonly AccountingRepository $repository)
    {
    }

    public function dashboardPayload(): array
    {
        return [
            'stats' => $this->repository->dashboardStats(),
            'recentInvoices' => $this->repository->recentInvoices(),
            'recentBills' => $this->repository->recentBills(),
            'pendingPayments' => $this->repository->pendingPayments(),
            'quickLinks' => [
                ['label' => 'Create Invoice', 'href' => '/accounting/invoices/create'],
                ['label' => 'Create Bill', 'href' => '/accounting/bills/create'],
                ['label' => 'Record Payment Received', 'href' => '/accounting/payments/create?direction=incoming'],
                ['label' => 'Record Payment Made', 'href' => '/accounting/payments/create?direction=outgoing'],
                ['label' => 'View All Invoices', 'href' => '/accounting/invoices'],
                ['label' => 'View All Bills', 'href' => '/accounting/bills'],
                ['label' => 'Chart of Accounts', 'href' => '/accounting/chart-of-accounts'],
                ['label' => 'Journal Entries', 'href' => '/accounting/journal-entries'],
            ],
        ];
    }

    public function createInvoice(array $data, ?string $userId): Invoice
    {
        return DB::transaction(function () use ($data, $userId) {
            $invoice = Invoice::query()->create([
                'invoice_number' => $data['invoice_number'] ?? $this->nextInvoiceNumber(),
                'contact_id' => $data['contact_id'] ?? null,
                'contact_name' => $data['contact_name'] ?? null,
                'organization_id' => $data['organization_id'] ?? null,
                'organization_name' => $data['organization_name'] ?? null,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'status' => $data['status'] ?? 'draft',
                'subtotal' => $data['subtotal'],
                'tax_amount' => $data['tax_amount'] ?? 0,
                'total_amount' => $data['total_amount'],
                'amount_paid' => 0,
                'amount_due' => $data['total_amount'],
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            foreach ($data['line_items'] ?? [] as $item) {
                InvoiceItem::query()->create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'total' => $item['total'] ?? (($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0)),
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            $entry = JournalEntry::query()->create([
                'entry_number' => $this->nextEntryNumber(),
                'entry_date' => $data['issue_date'],
                'description' => 'Invoice created: '.$invoice->invoice_number,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $invoice->update(['journal_entry_id' => $entry->id]);
            $this->recalculateInvoiceStatus($invoice, $userId);
            $this->log('invoices', $invoice->id, 'create', [], $invoice->fresh()->toArray(), $userId);

            return $invoice->fresh();
        });
    }

    public function updateInvoice(Invoice $invoice, array $data, ?string $userId): Invoice
    {
        return DB::transaction(function () use ($invoice, $data, $userId) {
            $old = $invoice->toArray();

            $invoice->update([
                'contact_id' => $data['contact_id'] ?? $invoice->contact_id,
                'contact_name' => $data['contact_name'] ?? $invoice->contact_name,
                'organization_id' => $data['organization_id'] ?? $invoice->organization_id,
                'organization_name' => $data['organization_name'] ?? $invoice->organization_name,
                'issue_date' => $data['issue_date'] ?? $invoice->issue_date,
                'due_date' => $data['due_date'] ?? $invoice->due_date,
                'subtotal' => $data['subtotal'] ?? $invoice->subtotal,
                'tax_amount' => $data['tax_amount'] ?? $invoice->tax_amount,
                'total_amount' => $data['total_amount'] ?? $invoice->total_amount,
                'amount_due' => ($data['total_amount'] ?? $invoice->total_amount) - $invoice->amount_paid,
                'notes' => $data['notes'] ?? $invoice->notes,
                'updated_by' => $userId,
            ]);

            $this->recalculateInvoiceStatus($invoice, $userId);
            $this->log('invoices', $invoice->id, 'update', $old, $invoice->fresh()->toArray(), $userId);

            return $invoice->fresh();
        });
    }

    public function recordInvoicePayment(Invoice $invoice, array $data, ?string $userId): Payment
    {
        return DB::transaction(function () use ($invoice, $data, $userId) {
            $payment = Payment::query()->create([
                'payment_number' => $data['payment_number'] ?? $this->nextPaymentNumber(),
                'invoice_id' => $invoice->id,
                'contact_id' => $invoice->contact_id,
                'amount' => $data['amount'],
                'payment_date' => $data['payment_date'],
                'method' => $data['method'],
                'reference' => $data['reference'] ?? null,
                'payment_direction' => 'incoming',
                'reconciliation_status' => 'pending',
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $invoice->update([
                'amount_paid' => $invoice->amount_paid + $payment->amount,
                'amount_due' => max(0, $invoice->total_amount - ($invoice->amount_paid + $payment->amount)),
                'updated_by' => $userId,
            ]);

            $this->recalculateInvoiceStatus($invoice, $userId);
            $this->log('payments', $payment->id, 'automation', [], $payment->toArray(), $userId, ['source' => 'form automation', 'rule' => 'record payment from invoice']);

            return $payment;
        });
    }

    public function createBill(array $data, ?string $userId): Bill
    {
        return DB::transaction(function () use ($data, $userId) {
            $bill = Bill::query()->create([
                'bill_number' => $data['bill_number'] ?? $this->nextBillNumber(),
                'vendor_id' => $data['vendor_id'],
                'vendor_name' => $data['vendor_name'] ?? null,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'status' => $data['status'] ?? 'draft',
                'subtotal' => $data['subtotal'],
                'tax_amount' => $data['tax_amount'] ?? 0,
                'total_amount' => $data['total_amount'],
                'amount_paid' => 0,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $this->log('bills', $bill->id, 'create', [], $bill->toArray(), $userId);

            return $bill;
        });
    }

    public function recordBillPayment(Bill $bill, array $data, ?string $userId): Payment
    {
        return DB::transaction(function () use ($bill, $data, $userId) {
            $payment = Payment::query()->create([
                'payment_number' => $data['payment_number'] ?? $this->nextPaymentNumber(),
                'bill_id' => $bill->id,
                'organization_id' => $bill->vendor_id,
                'amount' => $data['amount'],
                'payment_date' => $data['payment_date'],
                'method' => $data['method'],
                'reference' => $data['reference'] ?? null,
                'payment_direction' => 'outgoing',
                'reconciliation_status' => 'pending',
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $newPaid = $bill->amount_paid + $payment->amount;
            $bill->update([
                'amount_paid' => $newPaid,
                'status' => $newPaid >= $bill->total_amount ? 'paid' : $bill->status,
                'updated_by' => $userId,
            ]);

            $this->log('payments', $payment->id, 'automation', [], $payment->toArray(), $userId, ['source' => 'form automation', 'rule' => 'record payment from bill']);

            return $payment;
        });
    }

    public function recalculateInvoiceStatus(Invoice $invoice, ?string $userId): void
    {
        $status = $invoice->status;

        if ($invoice->amount_paid >= $invoice->total_amount) {
            $status = 'paid';
        } elseif ($invoice->amount_paid > 0) {
            $status = 'partial';
        } elseif (now()->toDateString() > optional($invoice->due_date)->toDateString()) {
            $status = 'overdue';
        }

        $invoice->update([
            'status' => $status,
            'updated_by' => $userId,
        ]);
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

    private function nextInvoiceNumber(): string
    {
        $last = Invoice::query()->orderByDesc('created_at')->value('invoice_number');
        $num = 1;
        if ($last && preg_match('/INV-(\d+)/', $last, $m)) {
            $num = ((int) $m[1]) + 1;
        }

        return sprintf('INV-%04d', $num);
    }

    private function nextBillNumber(): string
    {
        $last = Bill::query()->orderByDesc('created_at')->value('bill_number');
        $num = 1;
        if ($last && preg_match('/BILL-(\d+)/', $last, $m)) {
            $num = ((int) $m[1]) + 1;
        }

        return sprintf('BILL-%04d', $num);
    }

    private function nextPaymentNumber(): string
    {
        $last = Payment::query()->orderByDesc('created_at')->value('payment_number');
        $num = 1;
        if ($last && preg_match('/PAY-(\d+)/', $last, $m)) {
            $num = ((int) $m[1]) + 1;
        }

        return sprintf('PAY-%04d', $num);
    }

    private function nextEntryNumber(): string
    {
        $last = JournalEntry::query()->orderByDesc('created_at')->value('entry_number');
        $num = 1;
        if ($last && preg_match('/JE-(\d+)/', $last, $m)) {
            $num = ((int) $m[1]) + 1;
        }

        return sprintf('JE-%04d', $num);
    }
}
