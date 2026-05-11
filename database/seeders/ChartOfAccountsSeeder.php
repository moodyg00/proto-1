<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()
            ->where('email', 'admin@applab.test')
            ->orWhere('email', 'admin@admin.com')
            ->first();

        if (! $owner) {
            return;
        }

        $accounts = [
            ['code' => '1000', 'name' => 'Operating Cash', 'type' => 'asset', 'description' => 'Primary checking and operating cash.'],
            ['code' => '1100', 'name' => 'Accounts Receivable', 'type' => 'asset', 'description' => 'Open customer balances pending collection.'],
            ['code' => '1500', 'name' => 'Equipment', 'type' => 'asset', 'description' => 'Service equipment and durable tools.'],
            ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'liability', 'description' => 'Open vendor bills and unpaid purchases.'],
            ['code' => '2100', 'name' => 'Credit Card Payable', 'type' => 'liability', 'description' => 'Outstanding business card balance.'],
            ['code' => '3000', 'name' => 'Owner Equity', 'type' => 'equity', 'description' => 'Founding capital and retained owner balance.'],
            ['code' => '4000', 'name' => 'Service Revenue', 'type' => 'income', 'description' => 'Revenue from completed service work.'],
            ['code' => '4100', 'name' => 'Other Revenue', 'type' => 'income', 'description' => 'Non-core revenue and miscellaneous credits.'],
            ['code' => '5000', 'name' => 'Materials Expense', 'type' => 'expense', 'description' => 'Supplies and materials consumed on jobs.'],
            ['code' => '6100', 'name' => 'Contractor Expense', 'type' => 'expense', 'description' => 'Subcontractor and field labor costs.'],
        ];

        $seededAccounts = [];

        foreach ($accounts as $account) {
            $seededAccounts[$account['code']] = ChartOfAccount::query()->updateOrCreate(
                ['code' => $account['code']],
                [
                    ...$account,
                    'is_active' => true,
                    'created_by' => $owner->getKey(),
                    'updated_by' => $owner->getKey(),
                ],
            );
        }

        $entry = JournalEntry::query()->updateOrCreate(
            ['entry_number' => 'JE-OPEN-1000'],
            [
                'entry_date' => now()->startOfYear()->toDateString(),
                'description' => 'Opening balances for starter chart of accounts',
                'source_module' => 'accounting',
                'total_debits' => 18500,
                'total_credits' => 18500,
                'created_by' => $owner->getKey(),
                'updated_by' => $owner->getKey(),
            ],
        );

        $lines = [
            ['code' => '1000', 'debit' => 12000, 'credit' => 0, 'description' => 'Opening operating cash'],
            ['code' => '1100', 'debit' => 2500, 'credit' => 0, 'description' => 'Outstanding invoices at launch'],
            ['code' => '1500', 'debit' => 4000, 'credit' => 0, 'description' => 'Equipment placed in service'],
            ['code' => '2000', 'debit' => 0, 'credit' => 1800, 'description' => 'Outstanding vendor payables'],
            ['code' => '3000', 'debit' => 0, 'credit' => 16700, 'description' => 'Owner opening equity'],
        ];

        foreach ($lines as $line) {
            JournalEntryLine::query()->updateOrCreate(
                [
                    'journal_entry_id' => $entry->getKey(),
                    'account_id' => $seededAccounts[$line['code']]->getKey(),
                ],
                [
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'description' => $line['description'],
                    'created_by' => $owner->getKey(),
                    'updated_by' => $owner->getKey(),
                ],
            );
        }
    }
}