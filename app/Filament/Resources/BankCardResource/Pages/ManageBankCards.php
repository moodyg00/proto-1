<?php

namespace App\Filament\Resources\BankCardResource\Pages;

use App\Filament\Resources\BankCardResource;
use App\Filament\Resources\Pages\AppLabManageRecords;
use Filament\Actions;

class ManageBankCards extends AppLabManageRecords
{
    protected static string $resource = BankCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }
}