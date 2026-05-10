<?php

namespace App\Filament\Resources\WebContentResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\WebContentResource;
use Filament\Actions;

class ManageWebContents extends AppLabManageRecords
{
    protected static string $resource = WebContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }
}
