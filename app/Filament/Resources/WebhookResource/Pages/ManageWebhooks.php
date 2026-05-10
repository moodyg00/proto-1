<?php

namespace App\Filament\Resources\WebhookResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\WebhookResource;
use Filament\Actions;

class ManageWebhooks extends AppLabManageRecords
{
    protected static string $resource = WebhookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }
}
