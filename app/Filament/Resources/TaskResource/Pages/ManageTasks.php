<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\TaskResource;
use App\Models\Task;
use Filament\Actions;
use Illuminate\Database\Eloquent\Model;

class ManageTasks extends AppLabManageRecords
{
    protected static string $resource = TaskResource::class;

    protected function getKanbanStatusField(): ?string
    {
        return 'status';
    }

    protected function getKanbanStatuses(): array
    {
        return [
            'pending' => [
                'label' => 'Pending',
                'description' => 'Tasks waiting to be picked up.',
                'accent' => 'slate',
            ],
            'in_progress' => [
                'label' => 'In Progress',
                'description' => 'Tasks that are actively being worked.',
                'accent' => 'blue',
            ],
            'completed' => [
                'label' => 'Completed',
                'description' => 'Finished tasks ready for review.',
                'accent' => 'emerald',
            ],
            'cancelled' => [
                'label' => 'Cancelled',
                'description' => 'Tasks that were closed without completion.',
                'accent' => 'amber',
            ],
            'failed' => [
                'label' => 'Failed',
                'description' => 'Tasks that need another pass or intervention.',
                'accent' => 'rose',
            ],
        ];
    }

    protected function mutateKanbanRecordStatus(Model $record, string $status): void
    {
        if (! $record instanceof Task) {
            return;
        }

        $record->completed_at = $status === 'completed' ? ($record->completed_at ?? now()) : null;
    }

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }
}
