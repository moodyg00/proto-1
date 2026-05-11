<?php

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\CampaignResource;
use Filament\Actions;

class ManageCampaigns extends AppLabManageRecords
{
    protected static string $resource = CampaignResource::class;

    protected function getKanbanStatusField(): ?string
    {
        return 'status';
    }

    protected function getKanbanStatuses(): array
    {
        return [
            'active' => [
                'label' => 'Active',
                'description' => 'Campaigns currently spending and delivering.',
                'accent' => 'emerald',
            ],
            'paused' => [
                'label' => 'Paused',
                'description' => 'Campaigns temporarily stopped from running.',
                'accent' => 'amber',
            ],
            'completed' => [
                'label' => 'Completed',
                'description' => 'Campaigns that have finished their run.',
                'accent' => 'slate',
            ],
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }
}
