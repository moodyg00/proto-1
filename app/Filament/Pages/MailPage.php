<?php

namespace App\Filament\Pages;

use App\Models\Contact;
use App\Models\Lead;
use App\Models\Setting;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Schema;

class MailPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Customer Relations';

    protected static ?string $navigationLabel = 'Mail';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.mail-page';

    public function getMailMetrics(): array
    {
        return [
            'contacts_with_email' => Contact::query()->whereNotNull('email')->count(),
            'lead_follow_ups' => Lead::query()
                ->whereNotNull('next_follow_up')
                ->whereNotIn('status', ['converted', 'lost'])
                ->count(),
            'mail_settings' => $this->countIfTableExists('settings', fn (): int => Setting::query()
                ->where('module', 'crm')
                ->where('key', 'like', '%mail%')
                ->count()),
        ];
    }

    protected function countIfTableExists(string $table, callable $callback): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return (int) $callback();
    }
}