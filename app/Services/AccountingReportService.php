<?php

namespace App\Services;

use App\Models\AccountingReport;
use App\Models\BankTransaction;
use App\Models\Bill;
use App\Models\Invoice;
use App\Models\JournalEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AccountingReportService
{
    public static function typeOptions(): array
    {
        return [
            'open_invoices' => 'Open Invoices',
            'unpaid_bills' => 'Unpaid Bills',
            'bank_activity' => 'Bank Activity',
            'journal_entries' => 'Journal Entries',
        ];
    }

    public function exportPdf(AccountingReport $report): string
    {
        $payload = $this->build($report);
        $path = 'reports/accounting-report-' . $report->getKey() . '-' . now()->format('YmdHis') . '.pdf';
        $output = Pdf::loadView('reports.accounting-report-pdf', $payload)->output();

        Storage::disk('public')->put($path, $output);

        $report->forceFill([
            'last_generated_at' => now(),
            'last_export_path' => $path,
            'last_export_format' => 'pdf',
        ])->save();

        return $path;
    }

    public function emailReport(AccountingReport $report, string $recipient, string $subject, ?string $message): void
    {
        $path = $report->last_export_path ?: $this->exportPdf($report);
        $absolutePath = Storage::disk('public')->path($path);

        Mail::raw($message ?: 'Attached is your requested accounting report.', function ($mail) use ($recipient, $subject, $absolutePath, $report): void {
            $mail->to($recipient)
                ->subject($subject)
                ->attach($absolutePath, [
                    'as' => Str::slug($report->name) . '.pdf',
                    'mime' => 'application/pdf',
                ]);
        });
    }

    public function build(AccountingReport $report): array
    {
        [$startDate, $endDate] = $this->resolveDateRange($report);

        return match ($report->report_type) {
            'unpaid_bills' => $this->buildUnpaidBillsReport($report, $startDate, $endDate),
            'bank_activity' => $this->buildBankActivityReport($report, $startDate, $endDate),
            'journal_entries' => $this->buildJournalEntriesReport($report, $startDate, $endDate),
            default => $this->buildOpenInvoicesReport($report, $startDate, $endDate),
        };
    }

    protected function buildOpenInvoicesReport(AccountingReport $report, Carbon $startDate, Carbon $endDate): array
    {
        $records = Invoice::query()
            ->whereBetween('issue_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('amount_due', '>', 0)
            ->orderBy('due_date')
            ->get();

        return $this->formatPayload(
            $report,
            'Open Invoices',
            $startDate,
            $endDate,
            $records->map(fn (Invoice $invoice): array => [
                'reference' => $invoice->invoice_number,
                'party' => $invoice->contact_name ?: $invoice->organization_name ?: 'Unknown contact',
                'date' => optional($invoice->due_date)->format('M j, Y'),
                'status' => str($invoice->status)->headline()->toString(),
                'amount' => (float) ($invoice->amount_due ?? 0),
                'meta' => 'Issued ' . optional($invoice->issue_date)->format('M j, Y'),
            ])->all(),
            [
                ['label' => 'Open balance', 'value' => (float) $records->sum('amount_due')],
                ['label' => 'Records', 'value' => (float) $records->count()],
            ],
        );
    }

    protected function buildUnpaidBillsReport(AccountingReport $report, Carbon $startDate, Carbon $endDate): array
    {
        $records = Bill::query()
            ->whereBetween('issue_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereRaw('coalesce(total_amount, 0) > coalesce(amount_paid, 0)')
            ->orderBy('due_date')
            ->get();

        return $this->formatPayload(
            $report,
            'Unpaid Bills',
            $startDate,
            $endDate,
            $records->map(fn (Bill $bill): array => [
                'reference' => $bill->bill_number,
                'party' => $bill->vendor_name ?: 'Unknown vendor',
                'date' => optional($bill->due_date)->format('M j, Y'),
                'status' => str($bill->status)->headline()->toString(),
                'amount' => (float) (($bill->total_amount ?? 0) - ($bill->amount_paid ?? 0)),
                'meta' => 'Issued ' . optional($bill->issue_date)->format('M j, Y'),
            ])->all(),
            [
                ['label' => 'Outstanding payables', 'value' => (float) $records->sum(fn (Bill $bill): float => (float) (($bill->total_amount ?? 0) - ($bill->amount_paid ?? 0)))],
                ['label' => 'Records', 'value' => (float) $records->count()],
            ],
        );
    }

    protected function buildBankActivityReport(AccountingReport $report, Carbon $startDate, Carbon $endDate): array
    {
        $records = BankTransaction::query()
            ->with('bankAccount')
            ->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderByDesc('transaction_date')
            ->get();

        return $this->formatPayload(
            $report,
            'Bank Activity',
            $startDate,
            $endDate,
            $records->map(fn (BankTransaction $transaction): array => [
                'reference' => $transaction->reference ?: $transaction->id,
                'party' => $transaction->bankAccount?->name ?: 'Unknown account',
                'date' => optional($transaction->transaction_date)->format('M j, Y'),
                'status' => str($transaction->status)->headline()->toString(),
                'amount' => (float) ($transaction->amount ?? 0),
                'meta' => str($transaction->transaction_type)->headline()->toString() . ' | ' . ($transaction->description ?: 'No description'),
            ])->all(),
            [
                ['label' => 'Net movement', 'value' => (float) $records->sum('amount')],
                ['label' => 'Records', 'value' => (float) $records->count()],
            ],
        );
    }

    protected function buildJournalEntriesReport(AccountingReport $report, Carbon $startDate, Carbon $endDate): array
    {
        $records = JournalEntry::query()
            ->whereBetween('entry_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderByDesc('entry_date')
            ->get();

        return $this->formatPayload(
            $report,
            'Journal Entries',
            $startDate,
            $endDate,
            $records->map(fn (JournalEntry $entry): array => [
                'reference' => $entry->entry_number,
                'party' => $entry->source_module ?: 'General Ledger',
                'date' => optional($entry->entry_date)->format('M j, Y'),
                'status' => 'Balanced',
                'amount' => (float) ($entry->total_debits ?? 0),
                'meta' => $entry->description ?: 'No description',
            ])->all(),
            [
                ['label' => 'Debits', 'value' => (float) $records->sum('total_debits')],
                ['label' => 'Records', 'value' => (float) $records->count()],
            ],
        );
    }

    protected function formatPayload(AccountingReport $report, string $title, Carbon $startDate, Carbon $endDate, array $rows, array $summary): array
    {
        return [
            'report' => $report,
            'title' => $title,
            'periodLabel' => $startDate->format('M j, Y') . ' - ' . $endDate->format('M j, Y'),
            'rows' => $rows,
            'summary' => $summary,
            'generatedAt' => now(),
        ];
    }

    protected function resolveDateRange(AccountingReport $report): array
    {
        $filters = $report->filters ?? [];
        $startDate = filled($filters['start_date'] ?? null)
            ? Carbon::parse($filters['start_date'])->startOfDay()
            : now()->startOfMonth();
        $endDate = filled($filters['end_date'] ?? null)
            ? Carbon::parse($filters['end_date'])->endOfDay()
            : now()->endOfMonth();

        return [$startDate, $endDate];
    }
}