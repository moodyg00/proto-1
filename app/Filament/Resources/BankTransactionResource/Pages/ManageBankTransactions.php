<?php

namespace App\Filament\Resources\BankTransactionResource\Pages;

use App\Filament\Resources\BankTransactionResource;
use App\Filament\Resources\Pages\AppLabManageRecords;
use Filament\Actions;

class ManageBankTransactions extends AppLabManageRecords
{
    protected static string $resource = BankTransactionResource::class;

    protected function getKanbanStatusField(): ?string
    {
        return 'status';
    }

    protected function getKanbanStatuses(): array
    {
        return [
            'pending' => [
                'label' => 'Pending',
                'description' => 'Transactions that still need review or coding.',
                'accent' => 'slate',
            ],
            'categorized' => [
                'label' => 'Categorized',
                'description' => 'Transactions assigned to categories and ready to reconcile.',
                'accent' => 'blue',
            ],
            'reconciled' => [
                'label' => 'Reconciled',
                'description' => 'Transactions matched and closed out.',
                'accent' => 'emerald',
            ],
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }
}