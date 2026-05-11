<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use Filament\Actions;
use Illuminate\Database\Eloquent\Model;

class ManageInvoices extends AppLabManageRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getKanbanStatusField(): ?string
    {
        return 'status';
    }

    protected function getKanbanStatuses(): array
    {
        return [
            'draft' => [
                'label' => 'Draft',
                'description' => 'Invoices still being prepared before sending.',
                'accent' => 'slate',
            ],
            'sent' => [
                'label' => 'Sent',
                'description' => 'Invoices sent and awaiting payment.',
                'accent' => 'blue',
            ],
            'partial' => [
                'label' => 'Partial',
                'description' => 'Invoices with partial payment received.',
                'accent' => 'amber',
            ],
            'overdue' => [
                'label' => 'Overdue',
                'description' => 'Invoices past due and needing follow-up.',
                'accent' => 'rose',
            ],
            'paid' => [
                'label' => 'Paid',
                'description' => 'Invoices fully settled.',
                'accent' => 'emerald',
            ],
            'cancelled' => [
                'label' => 'Cancelled',
                'description' => 'Invoices voided or withdrawn from collection.',
                'accent' => 'slate',
            ],
        ];
    }

    protected function mutateKanbanRecordStatus(Model $record, string $status): void
    {
        if (! $record instanceof Invoice) {
            return;
        }

        $record->paid_at = $status === 'paid' ? ($record->paid_at ?? now()) : null;
    }

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }
}
