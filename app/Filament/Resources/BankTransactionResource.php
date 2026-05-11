<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankTransactionResource\Pages;
use App\Models\BankAccount;
use App\Models\BankCard;
use App\Models\BankTransaction;
use App\Models\JournalEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BankTransactionResource extends Resource
{
    protected static ?string $model = BankTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Banking';

    protected static ?string $navigationLabel = 'Transactions';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('bank_account_id')
                    ->label('Account')
                    ->options(fn (): array => BankAccount::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('card_id')
                    ->label('Card')
                    ->options(fn (): array => BankCard::query()->orderBy('card_name')->pluck('card_name', 'id')->all())
                    ->searchable()
                    ->preload(),
                Forms\Components\DatePicker::make('transaction_date')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                Forms\Components\Select::make('transaction_type')
                    ->options([
                        'deposit' => 'Deposit',
                        'withdrawal' => 'Withdrawal',
                        'transfer_in' => 'Transfer In',
                        'transfer_out' => 'Transfer Out',
                        'fee' => 'Fee',
                        'interest' => 'Interest',
                        'other' => 'Other',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('reference')
                    ->maxLength(120),
                Forms\Components\TextInput::make('external_category')
                    ->maxLength(120),
                Forms\Components\TextInput::make('internal_category')
                    ->maxLength(120),
                Forms\Components\Select::make('category_source')
                    ->options([
                        'mercury' => 'Mercury',
                        'manual' => 'Manual',
                        'rule' => 'Rule',
                    ])
                    ->default('manual')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'categorized' => 'Categorized',
                        'reconciled' => 'Reconciled',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\Select::make('journal_entry_id')
                    ->label('Journal Entry')
                    ->options(fn (): array => JournalEntry::query()->orderByDesc('entry_date')->pluck('entry_number', 'id')->all())
                    ->searchable(),
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('bankAccount.name')->label('Account')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('card.card_name')->label('Card')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('description')->limit(50)->searchable(),
                Tables\Columns\TextColumn::make('transaction_type')->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('amount')->money('USD')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('bank_account_id')
                    ->label('Account')
                    ->options(fn (): array => BankAccount::query()->orderBy('name')->pluck('name', 'id')->all()),
                Tables\Filters\SelectFilter::make('transaction_type')
                    ->options([
                        'deposit' => 'Deposit',
                        'withdrawal' => 'Withdrawal',
                        'transfer_in' => 'Transfer In',
                        'transfer_out' => 'Transfer Out',
                        'fee' => 'Fee',
                        'interest' => 'Interest',
                        'other' => 'Other',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'categorized' => 'Categorized',
                        'reconciled' => 'Reconciled',
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
            'index' => Pages\ManageBankTransactions::route('/'),
        ];
    }
}