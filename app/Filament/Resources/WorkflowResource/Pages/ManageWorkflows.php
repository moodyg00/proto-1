<?php

namespace App\Filament\Resources\WorkflowResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\WorkflowResource;
use Filament\Actions;

class ManageWorkflows extends AppLabManageRecords
{
    protected static string $resource = WorkflowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }
}
