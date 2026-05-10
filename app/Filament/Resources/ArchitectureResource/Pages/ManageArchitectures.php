<?php

namespace App\Filament\Resources\ArchitectureResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\ArchitectureResource;
use Filament\Actions;

class ManageArchitectures extends AppLabManageRecords
{
    protected static string $resource = ArchitectureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }
}
