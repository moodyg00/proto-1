<?php

namespace App\Filament\Resources\RecurringInvoiceResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\RecurringInvoiceResource;
use Filament\Actions;

class ManageRecurringInvoices extends AppLabManageRecords
{
    protected static string $resource = RecurringInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }
}