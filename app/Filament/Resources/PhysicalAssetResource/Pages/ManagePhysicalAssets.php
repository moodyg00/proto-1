<?php

namespace App\Filament\Resources\PhysicalAssetResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\PhysicalAssetResource;
use Filament\Actions;

class ManagePhysicalAssets extends AppLabManageRecords
{
    protected static string $resource = PhysicalAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }
}
