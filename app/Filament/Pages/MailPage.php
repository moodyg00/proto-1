<?php

namespace App\Filament\Pages;

use App\Models\Contact;
use App\Models\Lead;
use App\Models\Setting;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
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
        $templates = $this->getMailTemplates();
        $campaigns = $this->getMailCampaigns();
        $segments = $this->getRecipientSegments();

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
            'template_count' => count($templates),
            'scheduled_campaigns' => collect($campaigns)
                ->where('status', 'scheduled')
                ->count(),
            'segment_count' => count($segments),
        ];
    }

    public function getMailTemplates(): array
    {
        return $this->getStructuredSetting('crm', 'mail.templates', [
            [
                'name' => 'Estimate Follow-Up',
                'subject' => 'Checking in on your estimate',
                'channel' => 'sales',
                'body' => 'Hi {{first_name}}, just checking in on the estimate we sent over for {{service_name}}.',
            ],
            [
                'name' => 'Review Request',
                'subject' => 'How did we do?',
                'channel' => 'retention',
                'body' => 'Thanks again for choosing Moody Home Services. Would you mind leaving a quick review?',
            ],
        ]);
    }

    public function getMailCampaigns(): array
    {
        return $this->getStructuredSetting('crm', 'mail.campaigns', [
            [
                'name' => 'Spring Exterior Push',
                'template' => 'Estimate Follow-Up',
                'audience' => 'Open estimates',
                'status' => 'scheduled',
                'send_at' => now()->addDays(2)->format('Y-m-d H:i'),
            ],
        ]);
    }

    public function getRecipientSegments(): array
    {
        return [
            [
                'label' => 'Reachable Contacts',
                'count' => Contact::query()->whereNotNull('email')->count(),
                'description' => 'All contacts with a deliverable email address.',
            ],
            [
                'label' => 'Open Estimates',
                'count' => Lead::query()->whereIn('status', ['quoted', 'booked'])->count(),
                'description' => 'Leads that are actively moving through the pipeline.',
            ],
            [
                'label' => 'Due Follow-Ups',
                'count' => Lead::query()->whereNotNull('next_follow_up')->whereDate('next_follow_up', '<=', now()->toDateString())->count(),
                'description' => 'Leads that should receive a touchpoint today.',
            ],
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('mailTemplates')
                ->label('Templates')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->fillForm(fn (): array => [
                    'templates' => $this->getMailTemplates(),
                ])
                ->form([
                    Forms\Components\Repeater::make('templates')
                        ->defaultItems(0)
                        ->collapsed()
                        ->reorderableWithButtons()
                        ->addActionLabel('Add template')
                        ->schema([
                            Forms\Components\TextInput::make('name')->required()->maxLength(120),
                            Forms\Components\TextInput::make('subject')->required()->maxLength(255),
                            Forms\Components\Select::make('channel')
                                ->options([
                                    'sales' => 'Sales',
                                    'retention' => 'Retention',
                                    'promotional' => 'Promotional',
                                    'service' => 'Service',
                                ])
                                ->native(false)
                                ->required(),
                            Forms\Components\Textarea::make('body')
                                ->rows(5)
                                ->required()
                                ->columnSpanFull(),
                        ])
                        ->columns(3)
                        ->columnSpanFull(),
                ])
                ->action(function (array $data): void {
                    $this->upsertStructuredSetting(
                        'crm',
                        'mail.templates',
                        array_values($data['templates'] ?? []),
                    );

                    Notification::make()
                        ->title('Mail templates updated')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('mailCampaigns')
                ->label('Campaigns')
                ->icon('heroicon-o-paper-airplane')
                ->color('gray')
                ->fillForm(fn (): array => [
                    'campaigns' => $this->getMailCampaigns(),
                ])
                ->form([
                    Forms\Components\Repeater::make('campaigns')
                        ->defaultItems(0)
                        ->collapsed()
                        ->reorderableWithButtons()
                        ->addActionLabel('Add campaign')
                        ->schema([
                            Forms\Components\TextInput::make('name')->required()->maxLength(120),
                            Forms\Components\TextInput::make('template')->required()->maxLength(120),
                            Forms\Components\TextInput::make('audience')->required()->maxLength(120),
                            Forms\Components\Select::make('status')
                                ->options([
                                    'draft' => 'Draft',
                                    'scheduled' => 'Scheduled',
                                    'sent' => 'Sent',
                                ])
                                ->native(false)
                                ->required(),
                            Forms\Components\DateTimePicker::make('send_at'),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                ])
                ->action(function (array $data): void {
                    $this->upsertStructuredSetting(
                        'crm',
                        'mail.campaigns',
                        array_values($data['campaigns'] ?? []),
                    );

                    Notification::make()
                        ->title('Mail campaigns updated')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function countIfTableExists(string $table, callable $callback): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return (int) $callback();
    }

    protected function getStructuredSetting(string $module, string $key, array $default = []): array
    {
        $value = Setting::query()
            ->where('module', $module)
            ->where('key', $key)
            ->value('value');

        return is_array($value) ? $value : $default;
    }

    protected function upsertStructuredSetting(string $module, string $key, array $value): void
    {
        $setting = Setting::query()->firstOrNew([
            'module' => $module,
            'key' => $key,
        ]);

        $setting->fill([
            'module' => $module,
            'key' => $key,
            'value' => $value,
            'description' => 'CRM mail dashboard configuration.',
            'is_sensitive' => false,
        ]);

        $setting->save();
    }
}