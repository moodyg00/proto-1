<?php

namespace App\Filament\Resources\AccountingReportResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\AccountingReportResource;
use Filament\Actions;

class ManageAccountingReports extends AppLabManageRecords
{
    protected static string $resource = AccountingReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }
}
