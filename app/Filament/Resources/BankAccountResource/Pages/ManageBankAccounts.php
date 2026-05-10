<?php

namespace App\Filament\Resources\BankAccountResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\BankAccountResource;
use Filament\Actions;

class ManageBankAccounts extends AppLabManageRecords
{
    protected static string $resource = BankAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }
}
