<?php

namespace App\Filament\Resources\SocialMediaPostResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\SocialMediaPostResource;
use App\Models\SocialMediaPost;
use Filament\Actions;
use Illuminate\Database\Eloquent\Model;

class ManageSocialMediaPosts extends AppLabManageRecords
{
    protected static string $resource = SocialMediaPostResource::class;

    protected function getKanbanStatusField(): ?string
    {
        return 'status';
    }

    protected function getKanbanStatuses(): array
    {
        return [
            'new' => [
                'label' => 'New',
                'description' => 'New ideas that still need copy and creative direction.',
                'accent' => 'slate',
            ],
            'draft' => [
                'label' => 'Draft',
                'description' => 'Posts being written, reviewed, and proofed.',
                'accent' => 'amber',
            ],
            'scheduled' => [
                'label' => 'Scheduled',
                'description' => 'Approved posts ready to publish automatically.',
                'accent' => 'blue',
            ],
            'posted' => [
                'label' => 'Posted',
                'description' => 'Posts already sent live to the selected channel.',
                'accent' => 'emerald',
            ],
        ];
    }

    protected function getDefaultViewType(): string
    {
        return 'kanban';
    }

    protected function mutateKanbanRecordStatus(Model $record, string $status): void
    {
        if (! $record instanceof SocialMediaPost) {
            return;
        }

        if ($status === 'posted') {
            $record->scheduled_at = $record->scheduled_at ?? now();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }
}
