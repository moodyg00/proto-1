<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JournalEntryResource\Pages;
use App\Models\JournalEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JournalEntryResource extends Resource
{
    protected static ?string $model = JournalEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Accounting';

    protected static ?string $navigationLabel = 'Journal Entry';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('entry_number')
                    ->required()
                    ->maxLength(40),
                Forms\Components\DatePicker::make('entry_date')
                    ->required(),
                Forms\Components\TextInput::make('source_module')
                    ->maxLength(60),
                Forms\Components\TextInput::make('total_debits')
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('total_credits')
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('entry_number')->label('Entry #')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('entry_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('description')->limit(50)->searchable(),
                Tables\Columns\TextColumn::make('source_module')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('total_debits')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('total_credits')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('source_module')
                    ->options([
                        'accounting' => 'Accounting',
                        'banking' => 'Banking',
                        'invoicing' => 'Invoicing',
                        'operations' => 'Operations',
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
            'index' => Pages\ManageJournalEntries::route('/'),
        ];
    }
}
