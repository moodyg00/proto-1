<?php

namespace App\Filament\Pages;

use App\Models\BankTransaction;
use App\Models\Bill;
use App\Models\Invoice;
use App\Models\JournalEntry;
use Filament\Pages\Page;

class AccountingDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Accounting';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.accounting-dashboard';

    public function getKpis(): array
    {
        return [
            'open_invoice_balance' => (float) Invoice::query()->sum('amount_due'),
            'unpaid_bills' => (float) Bill::query()->where('status', '!=', 'paid')->sum('total_amount'),
            'journal_entries_this_month' => JournalEntry::query()
                ->whereBetween('entry_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
            'uncategorized_transactions' => BankTransaction::query()
                ->where('status', 'pending')
                ->count(),
        ];
    }

    public function getRecentInvoices(): array
    {
        return Invoice::query()
            ->orderByDesc('issue_date')
            ->limit(5)
            ->get(['invoice_number', 'contact_name', 'status', 'amount_due', 'issue_date'])
            ->map(fn (Invoice $invoice): array => [
                'number' => $invoice->invoice_number,
                'contact' => $invoice->contact_name,
                'status' => $invoice->status,
                'amount_due' => (float) $invoice->amount_due,
                'issue_date' => optional($invoice->issue_date)?->format('M j, Y'),
            ])
            ->all();
    }

    public function getRecentBills(): array
    {
        return Bill::query()
            ->orderByDesc('issue_date')
            ->limit(5)
            ->get(['bill_number', 'vendor_name', 'status', 'total_amount', 'issue_date'])
            ->map(fn (Bill $bill): array => [
                'number' => $bill->bill_number,
                'vendor' => $bill->vendor_name,
                'status' => $bill->status,
                'total_amount' => (float) $bill->total_amount,
                'issue_date' => optional($bill->issue_date)?->format('M j, Y'),
            ])
            ->all();
    }
}
