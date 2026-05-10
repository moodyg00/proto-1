<?php

namespace App\Filament\Resources\CrmSettingResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\CrmSettingResource;
use Filament\Actions;

class ManageCrmSettings extends AppLabManageRecords
{
    protected static string $resource = CrmSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }
}