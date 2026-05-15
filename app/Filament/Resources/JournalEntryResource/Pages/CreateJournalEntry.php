<?php

namespace App\Filament\Resources\JournalEntryResource\Pages;

use App\Filament\Resources\JournalEntryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateJournalEntry extends CreateRecord
{
    protected static string $resource = JournalEntryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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
}