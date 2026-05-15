<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PublicBookingLinkResource\Pages;
use App\Models\PublicBookingLink;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PublicBookingLinkResource extends Resource
{
    protected static ?string $model = PublicBookingLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Booking Links';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('service_id')
                    ->label('Default service')
                    ->options(fn (): array => Service::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload(),
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\CheckboxList::make('available_weekdays')
                    ->options([
                        1 => 'Mon',
                        2 => 'Tue',
                        3 => 'Wed',
                        4 => 'Thu',
                        5 => 'Fri',
                        6 => 'Sat',
                        7 => 'Sun',
                    ])
                    ->default([1, 2, 3, 4, 5])
                    ->columns(7),
                Forms\Components\TimePicker::make('start_time')
                    ->seconds(false)
                    ->required(),
                Forms\Components\TimePicker::make('end_time')
                    ->seconds(false)
                    ->required()
                    ->after('start_time'),
                Forms\Components\TextInput::make('slot_minutes')
                    ->numeric()
                    ->default(60)
                    ->required(),
                Forms\Components\TextInput::make('max_days_ahead')
                    ->numeric()
                    ->default(30)
                    ->required(),
                Forms\Components\TextInput::make('timezone')
                    ->default('America/New_York')
                    ->required(),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->seconds(false)
                    ->native(false),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
                Forms\Components\Placeholder::make('public_url')
                    ->label('Public URL')
                    ->content(fn (?PublicBookingLink $record): string => $record?->public_url ?? 'Save this link to generate the public URL.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Service')
                    ->placeholder('Any service'),
                Tables\Columns\TextColumn::make('public_url')
                    ->label('Public link')
                    ->copyable()
                    ->limit(36),
                Tables\Columns\TextColumn::make('slot_minutes')
                    ->label('Slot')
                    ->suffix(' min'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->since()
                    ->placeholder('Never'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active status'),
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
            'index' => Pages\ManagePublicBookingLinks::route('/'),
        ];
    }
}