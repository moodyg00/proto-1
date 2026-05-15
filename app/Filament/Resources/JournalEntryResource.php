<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JournalEntryResource\Pages;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

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
                Forms\Components\Section::make('Entry Summary')
                    ->schema([
                        Forms\Components\TextInput::make('entry_number')
                            ->required()
                            ->maxLength(40)
                            ->default(fn (): string => 'JE-' . now()->format('Ymd-His')),
                        Forms\Components\DatePicker::make('entry_date')
                            ->required()
                            ->default(now()),
                        Forms\Components\Select::make('source_module')
                            ->options([
                                'accounting' => 'Accounting',
                                'banking' => 'Banking',
                                'invoicing' => 'Invoicing',
                                'operations' => 'Operations',
                                'manual' => 'Manual',
                            ])
                            ->default('manual')
                            ->native(false),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('Double-Entry Lines')
                    ->description('Each entry must balance before it can be saved.')
                    ->schema([
                        Forms\Components\Repeater::make('lines')
                            ->relationship()
                            ->defaultItems(2)
                            ->reorderableWithButtons()
                            ->addActionLabel('Add line')
                            ->schema([
                                Forms\Components\Select::make('account_id')
                                    ->label('Account')
                                    ->options(fn (): array => ChartOfAccount::query()
                                        ->orderBy('code')
                                        ->get()
                                        ->mapWithKeys(fn (ChartOfAccount $account): array => [
                                            $account->getKey() => trim(($account->code ?: '0000') . ' - ' . $account->name),
                                        ])
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\TextInput::make('description')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('debit')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->prefix('$')
                                    ->required(),
                                Forms\Components\TextInput::make('credit')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->prefix('$')
                                    ->required(),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                        Forms\Components\Placeholder::make('entry_totals')
                            ->label('Balance Check')
                            ->content(function (Forms\Get $get): string {
                                [$totalDebits, $totalCredits] = static::calculateLineTotals($get('lines') ?? []);

                                $status = abs($totalDebits - $totalCredits) < 0.005 ? 'Balanced' : 'Out of balance';

                                return sprintf(
                                    'Debits: $%s | Credits: $%s | %s',
                                    number_format($totalDebits, 2),
                                    number_format($totalCredits, 2),
                                    $status,
                                );
                            }),
                    ]),
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
            ->recordUrl(fn (JournalEntry $record): string => static::getUrl('edit', ['record' => $record]))
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
            'create' => Pages\CreateJournalEntry::route('/create'),
            'edit' => Pages\EditJournalEntry::route('/{record}/edit'),
        ];
    }

    public static function calculateLineTotals(array $lines): array
    {
        $totalDebits = collect($lines)->sum(fn (array $line): float => (float) ($line['debit'] ?? 0));
        $totalCredits = collect($lines)->sum(fn (array $line): float => (float) ($line['credit'] ?? 0));

        return [round($totalDebits, 2), round($totalCredits, 2)];
    }
}
