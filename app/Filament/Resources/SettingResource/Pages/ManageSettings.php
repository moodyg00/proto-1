<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\SettingResource;
use App\Models\Setting;
use App\Support\BrandSettings;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Storage;

class ManageSettings extends AppLabManageRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('brandSettings')
                ->label('Business Branding')
                ->icon('heroicon-o-photo')
                ->color('gray')
                ->fillForm(fn (): array => [
                    'brand_name' => BrandSettings::name(),
                    'logo' => BrandSettings::get()['logo_path'] ?? null,
                ])
                ->form([
                    Forms\Components\TextInput::make('brand_name')
                        ->label('Business Name')
                        ->required()
                        ->maxLength(120),
                    Forms\Components\FileUpload::make('logo')
                        ->label('Header Logo')
                        ->disk('public')
                        ->directory('branding')
                        ->image()
                        ->visibility('public')
                        ->imagePreviewHeight('80')
                        ->helperText('Uploading a new logo replaces the previous stored file.'),
                ])
                ->action(function (array $data): void {
                    $setting = Setting::query()->firstOrNew([
                        'module' => 'business',
                        'key' => 'branding',
                    ]);

                    $current = is_array($setting->value) ? $setting->value : [];
                    $currentLogoPath = $current['logo_path'] ?? null;
                    $newLogoPath = $data['logo'] ?: $currentLogoPath;

                    if (filled($currentLogoPath) && $newLogoPath !== $currentLogoPath) {
                        Storage::disk('public')->delete($currentLogoPath);
                    }

                    $setting->fill([
                        'module' => 'business',
                        'key' => 'branding',
                        'value' => [
                            'brand_name' => $data['brand_name'],
                            'logo_path' => $newLogoPath,
                        ],
                        'description' => 'Business branding including the application name and header logo.',
                        'is_sensitive' => false,
                    ]);
                    $setting->save();

                    BrandSettings::clearCache();

                    Notification::make()
                        ->title('Branding updated')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('pipelineSettings')
                ->label('Pipelines')
                ->icon('heroicon-o-funnel')
                ->color('gray')
                ->modalWidth(MaxWidth::SevenExtraLarge)
                ->fillForm(fn (): array => [
                    'pipelines' => $this->getStructuredSetting('customer_relations', 'lead_pipelines', [
                        [
                            'id' => 'default',
                            'name' => 'Default Pipeline',
                            'description' => 'Primary lead pipeline for Moody Home Services.',
                            'is_default' => true,
                            'is_active' => true,
                            'stages' => [
                                ['key' => 'uncontacted', 'label' => 'New'],
                                ['key' => 'contacted', 'label' => 'Follow Up'],
                                ['key' => 'quoted', 'label' => 'Prospect'],
                                ['key' => 'booked', 'label' => 'Negotiation'],
                                ['key' => 'converted', 'label' => 'Won'],
                                ['key' => 'lost', 'label' => 'Lost'],
                            ],
                        ],
                    ]),
                ])
                ->form([
                    Forms\Components\Repeater::make('pipelines')
                        ->label('Lead Pipelines')
                        ->defaultItems(0)
                        ->collapsed()
                        ->reorderableWithButtons()
                        ->addActionLabel('Add pipeline')
                        ->schema([
                            Forms\Components\TextInput::make('id')
                                ->label('Pipeline key')
                                ->required()
                                ->maxLength(50),
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(120),
                            Forms\Components\Textarea::make('description')
                                ->rows(2)
                                ->columnSpanFull(),
                            Forms\Components\Toggle::make('is_default')
                                ->label('Default pipeline'),
                            Forms\Components\Toggle::make('is_active')
                                ->label('Active')
                                ->default(true),
                            Forms\Components\Repeater::make('stages')
                                ->label('Pipeline Stages')
                                ->defaultItems(0)
                                ->collapsed()
                                ->reorderableWithButtons()
                                ->addActionLabel('Add stage')
                                ->schema([
                                    Forms\Components\TextInput::make('key')
                                        ->required()
                                        ->maxLength(50),
                                    Forms\Components\TextInput::make('label')
                                        ->required()
                                        ->maxLength(120),
                                ])
                                ->columns(2)
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                ])
                ->action(function (array $data): void {
                    $pipelines = collect($data['pipelines'] ?? [])
                        ->map(function (array $pipeline, int $index): array {
                            return [
                                'id' => filled($pipeline['id'] ?? null) ? $pipeline['id'] : 'pipeline_' . ($index + 1),
                                'name' => $pipeline['name'] ?? 'Pipeline',
                                'description' => $pipeline['description'] ?? null,
                                'is_default' => (bool) ($pipeline['is_default'] ?? false),
                                'is_active' => (bool) ($pipeline['is_active'] ?? true),
                                'stages' => collect($pipeline['stages'] ?? [])
                                    ->map(fn (array $stage): array => [
                                        'key' => $stage['key'] ?? null,
                                        'label' => $stage['label'] ?? null,
                                    ])
                                    ->filter(fn (array $stage): bool => filled($stage['key']) && filled($stage['label']))
                                    ->values()
                                    ->all(),
                            ];
                        })
                        ->values()
                        ->all();

                    $this->upsertStructuredSetting(
                        'customer_relations',
                        'lead_pipelines',
                        $pipelines,
                        'Lead pipelines and their configurable stages.',
                    );

                    Notification::make()
                        ->title('Pipeline settings updated')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('kanbanBoardSettings')
                ->label('Kanban Boards')
                ->icon('heroicon-o-view-columns')
                ->color('gray')
                ->modalWidth(MaxWidth::SevenExtraLarge)
                ->fillForm(fn (): array => [
                    'boards' => $this->getStructuredSetting('operations', 'kanban_boards', [
                        [
                            'entity' => 'leads',
                            'default_view' => 'kanban',
                            'default_pipeline' => 'default',
                            'columns' => [
                                ['key' => 'uncontacted', 'label' => 'New', 'accent' => 'slate', 'show' => true],
                                ['key' => 'contacted', 'label' => 'Follow Up', 'accent' => 'blue', 'show' => true],
                                ['key' => 'quoted', 'label' => 'Prospect', 'accent' => 'amber', 'show' => true],
                                ['key' => 'booked', 'label' => 'Negotiation', 'accent' => 'emerald', 'show' => true],
                                ['key' => 'converted', 'label' => 'Won', 'accent' => 'teal', 'show' => true],
                                ['key' => 'lost', 'label' => 'Lost', 'accent' => 'rose', 'show' => true],
                            ],
                        ],
                        [
                            'entity' => 'jobs',
                            'default_view' => 'kanban',
                            'default_pipeline' => null,
                            'columns' => [
                                ['key' => 'new', 'label' => 'New', 'accent' => 'slate', 'show' => true],
                                ['key' => 'scheduled', 'label' => 'Scheduled', 'accent' => 'blue', 'show' => true],
                                ['key' => 'in_progress', 'label' => 'In Progress', 'accent' => 'amber', 'show' => true],
                                ['key' => 'completed', 'label' => 'Completed', 'accent' => 'emerald', 'show' => true],
                                ['key' => 'rework', 'label' => 'Rework', 'accent' => 'rose', 'show' => true],
                            ],
                        ],
                    ]),
                ])
                ->form([
                    Forms\Components\Repeater::make('boards')
                        ->label('Board Configurations')
                        ->defaultItems(0)
                        ->collapsed()
                        ->reorderableWithButtons()
                        ->addActionLabel('Add board config')
                        ->schema([
                            Forms\Components\Select::make('entity')
                                ->options([
                                    'leads' => 'Leads',
                                    'jobs' => 'Work Orders',
                                    'tasks' => 'Tasks',
                                    'social_media_posts' => 'Social Media Posts',
                                ])
                                ->required()
                                ->native(false),
                            Forms\Components\Select::make('default_view')
                                ->options([
                                    'list' => 'List',
                                    'card' => 'Card',
                                    'kanban' => 'Kanban',
                                ])
                                ->required()
                                ->native(false),
                            Forms\Components\TextInput::make('default_pipeline')
                                ->label('Default pipeline key')
                                ->maxLength(50),
                            Forms\Components\Repeater::make('columns')
                                ->label('Columns')
                                ->defaultItems(0)
                                ->collapsed()
                                ->reorderableWithButtons()
                                ->addActionLabel('Add column')
                                ->schema([
                                    Forms\Components\TextInput::make('key')
                                        ->required()
                                        ->maxLength(50),
                                    Forms\Components\TextInput::make('label')
                                        ->required()
                                        ->maxLength(120),
                                    Forms\Components\Select::make('accent')
                                        ->options([
                                            'slate' => 'Slate',
                                            'blue' => 'Blue',
                                            'amber' => 'Amber',
                                            'emerald' => 'Emerald',
                                            'teal' => 'Teal',
                                            'rose' => 'Rose',
                                        ])
                                        ->default('slate')
                                        ->required()
                                        ->native(false),
                                    Forms\Components\Toggle::make('show')
                                        ->label('Show on board')
                                        ->default(true),
                                ])
                                ->columns(4)
                                ->columnSpanFull(),
                        ])
                        ->columns(3)
                        ->columnSpanFull(),
                ])
                ->action(function (array $data): void {
                    $boards = collect($data['boards'] ?? [])
                        ->map(function (array $board): array {
                            return [
                                'entity' => $board['entity'] ?? null,
                                'default_view' => $board['default_view'] ?? 'list',
                                'default_pipeline' => $board['default_pipeline'] ?? null,
                                'columns' => collect($board['columns'] ?? [])
                                    ->map(fn (array $column): array => [
                                        'key' => $column['key'] ?? null,
                                        'label' => $column['label'] ?? null,
                                        'accent' => $column['accent'] ?? 'slate',
                                        'show' => (bool) ($column['show'] ?? true),
                                    ])
                                    ->filter(fn (array $column): bool => filled($column['key']) && filled($column['label']))
                                    ->values()
                                    ->all(),
                            ];
                        })
                        ->filter(fn (array $board): bool => filled($board['entity']))
                        ->values()
                        ->all();

                    $this->upsertStructuredSetting(
                        'operations',
                        'kanban_boards',
                        $boards,
                        'Default board layouts, columns, and preferred view types.',
                    );

                    Notification::make()
                        ->title('Kanban settings updated')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('customFieldSettings')
                ->label('Custom Fields')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('gray')
                ->modalWidth(MaxWidth::SevenExtraLarge)
                ->fillForm(fn (): array => [
                    'fields' => collect($this->getStructuredSetting('business', 'custom_fields', []))
                        ->map(function (array $field): array {
                            $field['options_text'] = collect($field['options'] ?? [])->implode(', ');

                            return $field;
                        })
                        ->all(),
                ])
                ->form([
                    Forms\Components\Repeater::make('fields')
                        ->label('Custom Field Definitions')
                        ->defaultItems(0)
                        ->collapsed()
                        ->reorderableWithButtons()
                        ->addActionLabel('Add custom field')
                        ->schema([
                            Forms\Components\Select::make('entity')
                                ->options([
                                    'leads' => 'Leads',
                                    'contacts' => 'Contacts',
                                    'jobs' => 'Work Orders',
                                    'tasks' => 'Tasks',
                                    'offerings' => 'Offerings',
                                    'blog_posts' => 'Blog Posts',
                                    'social_media_posts' => 'Social Media Posts',
                                ])
                                ->required()
                                ->native(false),
                            Forms\Components\Select::make('module')
                                ->options(SettingResource::getModuleOptions())
                                ->required()
                                ->native(false),
                            Forms\Components\TextInput::make('label')
                                ->required()
                                ->maxLength(120),
                            Forms\Components\TextInput::make('key')
                                ->required()
                                ->maxLength(50),
                            Forms\Components\Select::make('type')
                                ->options([
                                    'text' => 'Text',
                                    'textarea' => 'Textarea',
                                    'number' => 'Number',
                                    'select' => 'Select',
                                    'toggle' => 'Toggle',
                                    'date' => 'Date',
                                ])
                                ->required()
                                ->native(false),
                            Forms\Components\TextInput::make('options_text')
                                ->label('Options')
                                ->helperText('Comma-separated for select fields.'),
                            Forms\Components\TextInput::make('help_text')
                                ->maxLength(255)
                                ->columnSpanFull(),
                            Forms\Components\Toggle::make('required')
                                ->label('Required'),
                            Forms\Components\Toggle::make('is_active')
                                ->label('Active')
                                ->default(true),
                        ])
                        ->columns(3)
                        ->columnSpanFull(),
                ])
                ->action(function (array $data): void {
                    $fields = collect($data['fields'] ?? [])
                        ->map(function (array $field): array {
                            return [
                                'entity' => $field['entity'] ?? null,
                                'module' => $field['module'] ?? 'business',
                                'label' => $field['label'] ?? null,
                                'key' => $field['key'] ?? null,
                                'type' => $field['type'] ?? 'text',
                                'options' => collect(explode(',', (string) ($field['options_text'] ?? '')))
                                    ->map(fn (string $option): string => trim($option))
                                    ->filter()
                                    ->values()
                                    ->all(),
                                'help_text' => $field['help_text'] ?? null,
                                'required' => (bool) ($field['required'] ?? false),
                                'is_active' => (bool) ($field['is_active'] ?? true),
                            ];
                        })
                        ->filter(fn (array $field): bool => filled($field['entity']) && filled($field['label']) && filled($field['key']))
                        ->values()
                        ->all();

                    $this->upsertStructuredSetting(
                        'business',
                        'custom_fields',
                        $fields,
                        'Custom field definitions for major application records.',
                    );

                    Notification::make()
                        ->title('Custom fields updated')
                        ->success()
                        ->send();
                }),
            ...parent::getHeaderActions(),
            Actions\CreateAction::make(),
        ];
    }

    protected function getSupportedViewTypes(): array
    {
        return ['list'];
    }

    protected function getStructuredSetting(string $module, string $key, array $default = []): array
    {
        $value = Setting::query()
            ->where('module', $module)
            ->where('key', $key)
            ->value('value');

        return is_array($value) ? $value : $default;
    }

    protected function upsertStructuredSetting(string $module, string $key, array $value, string $description): void
    {
        $setting = Setting::query()->firstOrNew([
            'module' => $module,
            'key' => $key,
        ]);

        $setting->fill([
            'module' => $module,
            'key' => $key,
            'value' => $value,
            'description' => $description,
            'is_sensitive' => false,
        ]);

        $setting->save();
    }
}
