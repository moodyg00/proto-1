<?php

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\BlogPostResource;
use Filament\Actions;

class ManageBlogPosts extends AppLabManageRecords
{
    protected static string $resource = BlogPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }
}
