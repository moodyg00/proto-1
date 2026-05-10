<?php

namespace App\Filament\Resources\SocialMediaPostResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\SocialMediaPostResource;
use Filament\Actions;

class ManageSocialMediaPosts extends AppLabManageRecords
{
    protected static string $resource = SocialMediaPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }
}
