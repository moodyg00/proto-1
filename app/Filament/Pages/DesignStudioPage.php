<?php

namespace App\Filament\Pages;

use App\Models\PhysicalDesign;
use App\Models\PhysicalDesignVersion;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DesignStudioPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationGroup = 'Content & Blog';

    protected static ?string $navigationLabel = 'Design Studio';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'design-studio';

    protected static string $view = 'filament.pages.design-studio-page';

    public static function getDesignTypeOptions(): array
    {
        return [
            't_shirt' => 'T-Shirt',
            'business_card' => 'Business Card',
            'flyer' => 'Flyer',
            'sticker' => 'Sticker',
            'door_hanger' => 'Door Hanger',
            'yard_sign' => 'Yard Sign',
        ];
    }

    public static function getDefaultDimensionsForType(?string $type): string
    {
        return match ($type) {
            't_shirt' => '12x14',
            'business_card' => '3.5x2',
            'flyer' => '8.5x11',
            'sticker' => '3x3',
            'door_hanger' => '4.25x11',
            'yard_sign' => '24x18',
            default => '8.5x11',
        };
    }

    public function getDesigns(): array
    {
        return PhysicalDesign::query()
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (PhysicalDesign $design): array => [
                'id' => $design->getKey(),
                'name' => $design->name,
                'design_type' => $design->design_type,
                'dimensions' => $design->dimensions ?: static::getDefaultDimensionsForType($design->design_type),
                'status' => $design->status ?: 'draft',
                'updated_at' => optional($design->updated_at)->diffForHumans(),
            ])
            ->all();
    }

    public function getSelectedDesign(): ?PhysicalDesign
    {
        $designId = request()->query('design');

        return PhysicalDesign::query()
            ->when(filled($designId), fn ($query) => $query->whereKey($designId))
            ->orderByDesc('updated_at')
            ->first();
    }

    public function getArtboardData(): array
    {
        $design = $this->getSelectedDesign();
        $dimensions = $design?->dimensions ?: static::getDefaultDimensionsForType($design?->design_type);
        [$width, $height] = collect(explode('x', strtolower((string) $dimensions)))->map(fn (string $value): float => max((float) trim($value), 1))->pad(2, 1)->all();
        $scale = min(440 / $width, 360 / $height);

        return [
            'width' => (int) round($width * $scale),
            'height' => (int) round($height * $scale),
            'label' => $dimensions,
            'shape' => $design?->design_type ?: 'custom',
        ];
    }

    public function getSelectedAssetIds(): array
    {
        return array_values(array_filter(Arr::get($this->getSelectedDesign()?->files, 'asset_media_ids', [])));
    }

    public function getSelectedAssetLibrary(): array
    {
        $assetIds = $this->getSelectedAssetIds();

        if ($assetIds === []) {
            return [];
        }

        return Media::query()
            ->whereIn('id', $assetIds)
            ->get(['id', 'name', 'file_name', 'mime_type'])
            ->map(fn (Media $media): array => [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
            ])
            ->all();
    }

    public function getAssetLibrary(): array
    {
        return Media::query()
            ->latest('updated_at')
            ->limit(18)
            ->get(['id', 'name', 'file_name', 'mime_type'])
            ->map(fn (Media $media): array => [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
            ])
            ->all();
    }

    public function getDesignStats(): array
    {
        return [
            'total' => PhysicalDesign::query()->count(),
            'approved' => PhysicalDesign::query()->where('status', 'approved')->count(),
            'draft' => PhysicalDesign::query()->where('status', 'draft')->count(),
            'versions' => PhysicalDesignVersion::query()->count(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('newDesign')
                ->label('New Design')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->form($this->getDesignFormSchema())
                ->action(function (array $data): void {
                    $design = PhysicalDesign::query()->create($this->mutateDesignData($data));

                    Notification::make()
                        ->title('Design created')
                        ->success()
                        ->send();

                    $this->redirect(static::getUrl(['design' => $design->getKey()]));
                }),
            Actions\Action::make('editSelectedDesign')
                ->label('Edit Selected')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->visible(fn (): bool => $this->getSelectedDesign() !== null)
                ->fillForm(fn (): array => $this->getEditDesignFormData())
                ->form($this->getDesignFormSchema())
                ->action(function (array $data): void {
                    $design = $this->getSelectedDesign();

                    if (! $design) {
                        return;
                    }

                    $design->forceFill($this->mutateDesignData($data))->save();

                    Notification::make()
                        ->title('Design updated')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('newVersion')
                ->label('New Version')
                ->icon('heroicon-o-sparkles')
                ->color('success')
                ->visible(fn (): bool => $this->getSelectedDesign() !== null)
                ->form([
                    Forms\Components\TextInput::make('version_label')
                        ->label('Version notes')
                        ->maxLength(255),
                    Forms\Components\Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'review' => 'In Review',
                            'approved' => 'Approved',
                            'archived' => 'Archived',
                        ])
                        ->default('draft')
                        ->required(),
                    Forms\Components\CheckboxList::make('asset_media_ids')
                        ->label('Assets from library')
                        ->options($this->getMediaOptions())
                        ->columns(2),
                    Forms\Components\TextInput::make('preview_url')
                        ->label('Preview URL')
                        ->maxLength(500),
                    Forms\Components\TextInput::make('production_pdf')
                        ->label('Production PDF URL')
                        ->maxLength(500),
                    Forms\Components\Textarea::make('notes')
                        ->rows(4)
                        ->columnSpanFull(),
                ])
                ->action(function (array $data): void {
                    $design = $this->getSelectedDesign();

                    if (! $design) {
                        return;
                    }

                    $nextVersion = ((int) PhysicalDesignVersion::query()->where('physical_design_id', $design->getKey())->max('version_number')) + 1;

                    $version = PhysicalDesignVersion::query()->create([
                        'physical_design_id' => $design->getKey(),
                        'version_number' => $nextVersion,
                        'files' => array_filter([
                            'asset_media_ids' => array_values(array_filter($data['asset_media_ids'] ?? [])),
                            'preview_url' => $data['preview_url'] ?: null,
                            'production_pdf' => $data['production_pdf'] ?: null,
                        ]),
                        'notes' => $data['notes'] ?: $data['version_label'] ?: null,
                        'status' => $data['status'],
                    ]);

                    $design->forceFill([
                        'status' => $data['status'],
                        'latest_version_id' => $version->getKey(),
                        'files' => array_filter([
                            'asset_media_ids' => array_values(array_filter($data['asset_media_ids'] ?? [])),
                            'preview_url' => $data['preview_url'] ?: Arr::get($design->files, 'preview_url'),
                            'production_pdf' => $data['production_pdf'] ?: Arr::get($design->files, 'production_pdf'),
                            'pdf' => Arr::get($design->files, 'pdf'),
                        ]),
                    ])->save();

                    Notification::make()
                        ->title('New design version saved')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getDesignFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\Select::make('design_type')
                ->options(static::getDesignTypeOptions())
                ->required()
                ->live()
                ->afterStateUpdated(fn (Set $set, ?string $state): mixed => $set('dimensions', static::getDefaultDimensionsForType($state))),
            Forms\Components\TextInput::make('dimensions')
                ->helperText('Use width x height, such as 8.5x11 or 3.5x2.')
                ->required(),
            Forms\Components\Select::make('status')
                ->options([
                    'draft' => 'Draft',
                    'review' => 'In Review',
                    'approved' => 'Approved',
                    'archived' => 'Archived',
                ])
                ->default('draft')
                ->required(),
            Forms\Components\Textarea::make('description')
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\CheckboxList::make('asset_media_ids')
                ->label('Assets from library')
                ->options($this->getMediaOptions())
                ->columns(2)
                ->columnSpanFull(),
            Forms\Components\TextInput::make('preview_url')
                ->label('Preview URL')
                ->maxLength(500),
            Forms\Components\TextInput::make('production_pdf')
                ->label('Production PDF URL')
                ->maxLength(500),
        ];
    }

    protected function getEditDesignFormData(): array
    {
        $design = $this->getSelectedDesign();

        return [
            'name' => $design?->name,
            'design_type' => $design?->design_type,
            'dimensions' => $design?->dimensions ?: static::getDefaultDimensionsForType($design?->design_type),
            'status' => $design?->status ?: 'draft',
            'description' => $design?->description,
            'asset_media_ids' => array_values(array_filter(Arr::get($design?->files, 'asset_media_ids', []))),
            'preview_url' => Arr::get($design?->files, 'preview_url'),
            'production_pdf' => Arr::get($design?->files, 'production_pdf'),
        ];
    }

    protected function mutateDesignData(array $data): array
    {
        return [
            'name' => $data['name'],
            'design_type' => $data['design_type'],
            'dimensions' => $data['dimensions'],
            'status' => $data['status'],
            'description' => $data['description'] ?: null,
            'files' => array_filter([
                'asset_media_ids' => array_values(array_filter($data['asset_media_ids'] ?? [])),
                'preview_url' => $data['preview_url'] ?: null,
                'production_pdf' => $data['production_pdf'] ?: null,
            ]),
        ];
    }

    protected function getMediaOptions(): array
    {
        return Media::query()
            ->latest('updated_at')
            ->limit(48)
            ->get(['id', 'name', 'file_name'])
            ->mapWithKeys(fn (Media $media): array => [
                $media->id => trim(($media->name ?: Str::beforeLast($media->file_name, '.')) . ' - ' . $media->file_name),
            ])
            ->all();
    }
}