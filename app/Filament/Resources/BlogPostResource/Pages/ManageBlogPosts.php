<?php

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\BlogPostResource;
use App\Models\BlogPost;
use Filament\Actions;
use Illuminate\Database\Eloquent\Model;

class ManageBlogPosts extends AppLabManageRecords
{
    protected static string $resource = BlogPostResource::class;

    protected function getKanbanStatusField(): ?string
    {
        return 'status';
    }

    protected function getKanbanStatuses(): array
    {
        return [
            'draft' => [
                'label' => 'Draft',
                'description' => 'Posts still being written and edited.',
                'accent' => 'slate',
            ],
            'scheduled' => [
                'label' => 'Scheduled',
                'description' => 'Posts queued for a future publish date.',
                'accent' => 'blue',
            ],
            'published' => [
                'label' => 'Published',
                'description' => 'Live posts currently visible to readers.',
                'accent' => 'emerald',
            ],
            'archived' => [
                'label' => 'Archived',
                'description' => 'Posts removed from the active publishing queue.',
                'accent' => 'amber',
            ],
        ];
    }

    protected function mutateKanbanRecordStatus(Model $record, string $status): void
    {
        if (! $record instanceof BlogPost) {
            return;
        }

        if ($status === 'published') {
            $record->published_at = $record->published_at ?? now();

            return;
        }

        if (in_array($status, ['draft', 'archived'], true)) {
            $record->published_at = null;
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
