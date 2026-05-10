<?php

namespace App\Filament\Resources\McpServerResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\McpServerResource;
use Filament\Actions;

class ManageMcpServers extends AppLabManageRecords
{
    protected static string $resource = McpServerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }
}
