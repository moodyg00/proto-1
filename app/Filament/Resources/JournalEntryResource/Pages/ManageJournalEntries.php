<?php

namespace App\Filament\Resources\JournalEntryResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\JournalEntryResource;
use Filament\Actions;

class ManageJournalEntries extends AppLabManageRecords
{
    protected static string $resource = JournalEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }
}
