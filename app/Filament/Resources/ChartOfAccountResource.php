<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChartOfAccountResource\Pages;
use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;

class ChartOfAccountResource extends Resource
{
    protected static ?string $model = ChartOfAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationGroup = 'Accounting';

    protected static ?string $navigationLabel = 'Chart of Accounts';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(30),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options([
                        'asset' => 'Asset',
                        'liability' => 'Liability',
                        'equity' => 'Equity',
                        'income' => 'Income',
                        'expense' => 'Expense',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 1,
                'xl' => 1,
            ])
            ->columns([
                Split::make([
                    Stack::make([
                        Tables\Columns\TextColumn::make('name')
                            ->searchable()
                            ->sortable()
                            ->weight(FontWeight::Bold)
                            ->description(fn (ChartOfAccount $record): ?string => $record->description ?: null),
                        Tables\Columns\TextColumn::make('code')
                            ->searchable()
                            ->badge()
                            ->color('gray'),
                        Tables\Columns\TextColumn::make('type')
                            ->badge()
                            ->sortable(),
                    ]),
                    Stack::make([
                        Tables\Columns\TextColumn::make('account_value')
                            ->label('Value')
                            ->state(fn (ChartOfAccount $record): float => static::normalizeAccountValue($record))
                            ->money('USD')
                            ->weight(FontWeight::Bold),
                        Tables\Columns\IconColumn::make('is_active')
                            ->label('Active')
                            ->boolean(),
                    ]),
                ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'asset' => 'Asset',
                        'liability' => 'Liability',
                        'equity' => 'Equity',
                        'income' => 'Income',
                        'expense' => 'Expense',
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select('chart_of_accounts.*')
            ->selectSub(
                DB::table('journal_entry_lines')
                    ->selectRaw('COALESCE(SUM(debit - credit), 0)')
                    ->whereColumn('journal_entry_lines.account_id', 'chart_of_accounts.id'),
                'balance_delta',
            );
    }

    protected static function normalizeAccountValue(ChartOfAccount $record): float
    {
        $delta = (float) ($record->balance_delta ?? 0);

        $normalized = in_array($record->type, ['liability', 'equity', 'income'], true)
            ? $delta * -1
            : $delta;

        return abs($normalized) < 0.00001 ? 0.0 : $normalized;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageChartOfAccounts::route('/'),
        ];
    }
}
