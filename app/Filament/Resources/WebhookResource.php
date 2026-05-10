<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebhookResource\Pages;
use App\Models\ApiIntegration;
use App\Models\Webhook;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WebhookResource extends Resource
{
    protected static ?string $model = Webhook::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = 'Integrations';

    protected static ?string $navigationLabel = 'Webhooks';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('integration_id')
                    ->label('Integration')
                    ->options(fn (): array => ApiIntegration::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('direction')
                    ->options([
                        'incoming' => 'Incoming',
                        'outgoing' => 'Outgoing',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('event_type')
                    ->required()
                    ->maxLength(120),
                Forms\Components\TextInput::make('endpoint_url')
                    ->url(),
                Forms\Components\TextInput::make('secret')
                    ->password()
                    ->revealable(),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'failed' => 'Failed',
                        'disabled' => 'Disabled',
                    ])
                    ->default('active')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
                Forms\Components\DateTimePicker::make('last_triggered_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('integration.name')->label('Integration')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('direction')->badge()->sortable(),
                Tables\Columns\TextColumn::make('event_type')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('endpoint_url')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('last_triggered_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('direction')
                    ->options([
                        'incoming' => 'Incoming',
                        'outgoing' => 'Outgoing',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'failed' => 'Failed',
                        'disabled' => 'Disabled',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWebhooks::route('/'),
        ];
    }
}
