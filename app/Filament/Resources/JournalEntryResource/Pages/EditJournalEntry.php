<?php

namespace App\Filament\Resources\JournalEntryResource\Pages;

use App\Filament\Resources\JournalEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditJournalEntry extends EditRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        [$totalDebits, $totalCredits] = JournalEntryResource::calculateLineTotals($data['lines'] ?? []);

        if (abs($totalDebits - $totalCredits) >= 0.005) {
            throw ValidationException::withMessages([
                'data.lines' => 'Journal entry debits and credits must balance before saving.',
            ]);
        }

        $data['total_debits'] = $totalDebits;
        $data['total_credits'] = $totalCredits;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}