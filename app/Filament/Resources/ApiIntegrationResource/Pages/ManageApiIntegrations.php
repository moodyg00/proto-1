<?php

namespace App\Filament\Resources\ApiIntegrationResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\ApiIntegrationResource;
use Filament\Actions;

class ManageApiIntegrations extends AppLabManageRecords
{
    protected static string $resource = ApiIntegrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }
}
