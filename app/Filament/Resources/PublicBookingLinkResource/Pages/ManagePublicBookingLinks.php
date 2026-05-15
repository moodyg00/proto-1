<?php

namespace App\Filament\Resources\PublicBookingLinkResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\PublicBookingLinkResource;
use Filament\Actions;

class ManagePublicBookingLinks extends AppLabManageRecords
{
    protected static string $resource = PublicBookingLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }
}