<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AvailabilityResource\Pages;
use App\Models\Availability;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AvailabilityResource extends Resource
{
    protected static ?string $model = Availability::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Availability';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('scope')
                    ->options([
                        'admin' => 'Admin',
                        'contractor' => 'Contractor',
                        'public' => 'Public booking',
                    ])
                    ->default('contractor')
                    ->required(),
                Forms\Components\DateTimePicker::make('starts_at')
                    ->seconds(false)
                    ->native(false)
                    ->required(),
                Forms\Components\DateTimePicker::make('ends_at')
                    ->seconds(false)
                    ->native(false)
                    ->required()
                    ->after('starts_at'),
                Forms\Components\Toggle::make('is_available')
                    ->label('Visible as available')
                    ->default(true),
                Forms\Components\Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at')
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->searchable(),
                Tables\Columns\TextColumn::make('scope')
                    ->badge(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Ends')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_available')
                    ->label('Published')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('scope')
                    ->options([
                        'admin' => 'Admin',
                        'contractor' => 'Contractor',
                        'public' => 'Public booking',
                    ]),
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
            'index' => Pages\ManageAvailabilities::route('/'),
        ];
    }
}