<?php

namespace App\Filament\Resources\EstimateResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\EstimateResource;
use Filament\Actions;

class ManageEstimates extends AppLabManageRecords
{
    protected static string $resource = EstimateResource::class;

    protected function getKanbanStatusField(): ?string
    {
        return 'status';
    }

    protected function getKanbanStatuses(): array
    {
        return [
            'draft' => [
                'label' => 'Draft',
                'description' => 'Estimates still being prepared.',
                'accent' => 'slate',
            ],
            'sent' => [
                'label' => 'Sent',
                'description' => 'Estimates out with the customer.',
                'accent' => 'blue',
            ],
            'approved' => [
                'label' => 'Approved',
                'description' => 'Accepted estimates ready for fulfillment.',
                'accent' => 'emerald',
            ],
            'rejected' => [
                'label' => 'Rejected',
                'description' => 'Estimates that were declined.',
                'accent' => 'rose',
            ],
            'expired' => [
                'label' => 'Expired',
                'description' => 'Estimates that aged out without a decision.',
                'accent' => 'amber',
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