<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankTransactionResource\Pages;
use App\Models\BankAccount;
use App\Models\BankCard;
use App\Models\BankTransaction;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
                Tables\Columns\TextColumn::make('journalEntry.entry_number')->label('Journal Entry')->toggleable(),
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
                Tables\Actions\Action::make('linkJournalEntry')
                    ->label(fn (BankTransaction $record): string => $record->journal_entry_id ? 'Update Journal Link' : 'Link Journal Entry')
                    ->icon('heroicon-o-link')
                    ->color('primary')
                    ->fillForm(fn (BankTransaction $record): array => [
                        'link_mode' => $record->journal_entry_id ? 'existing' : 'new',
                        'existing_journal_entry_id' => $record->journal_entry_id,
                        'memo' => $record->description,
                    ])
                    ->form([
                        Forms\Components\Radio::make('link_mode')
                            ->options([
                                'existing' => 'Link an existing journal entry',
                                'new' => 'Create a new journal entry',
                            ])
                            ->default('new')
                            ->inline()
                            ->live()
                            ->required(),
                        Forms\Components\Select::make('existing_journal_entry_id')
                            ->label('Existing journal entry')
                            ->options(fn (): array => JournalEntry::query()->orderByDesc('entry_date')->get()
                                ->mapWithKeys(fn (JournalEntry $entry): array => [
                                    $entry->getKey() => $entry->entry_number . ' - ' . ($entry->description ?: 'No description'),
                                ])->all())
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get): bool => $get('link_mode') === 'existing')
                            ->required(fn (Get $get): bool => $get('link_mode') === 'existing'),
                        Forms\Components\Select::make('bank_account_chart_id')
                            ->label('Bank account GL account')
                            ->options(static::getChartOfAccountOptions())
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get): bool => $get('link_mode') === 'new')
                            ->required(fn (Get $get): bool => $get('link_mode') === 'new'),
                        Forms\Components\Select::make('offset_account_id')
                            ->label('Offset GL account')
                            ->options(static::getChartOfAccountOptions())
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get): bool => $get('link_mode') === 'new')
                            ->required(fn (Get $get): bool => $get('link_mode') === 'new'),
                        Forms\Components\Textarea::make('memo')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->action(function (BankTransaction $record, array $data): void {
                        if ($data['link_mode'] === 'existing') {
                            $record->forceFill([
                                'journal_entry_id' => $data['existing_journal_entry_id'],
                                'status' => 'categorized',
                                'updated_by' => Auth::id(),
                            ])->save();

                            Notification::make()
                                ->title('Journal entry linked')
                                ->success()
                                ->send();

                            return;
                        }

                        $journalEntry = JournalEntry::query()->create([
                            'entry_number' => 'JE-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                            'description' => $data['memo'] ?: ($record->description ?: 'Bank transaction journal entry'),
                            'entry_date' => $record->transaction_date,
                            'total_debits' => abs((float) $record->amount),
                            'total_credits' => abs((float) $record->amount),
                            'source_module' => 'banking',
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                        ]);

                        $isBankDebit = in_array($record->transaction_type, ['deposit', 'transfer_in', 'interest'], true);
                        $amount = abs((float) $record->amount);

                        $journalEntry->lines()->createMany([
                            [
                                'account_id' => $data['bank_account_chart_id'],
                                'description' => 'Bank side - ' . ($record->reference ?: $record->transaction_type),
                                'debit' => $isBankDebit ? $amount : 0,
                                'credit' => $isBankDebit ? 0 : $amount,
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                            ],
                            [
                                'account_id' => $data['offset_account_id'],
                                'description' => 'Offset - ' . ($record->reference ?: $record->transaction_type),
                                'debit' => $isBankDebit ? 0 : $amount,
                                'credit' => $isBankDebit ? $amount : 0,
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                            ],
                        ]);

                        $record->forceFill([
                            'journal_entry_id' => $journalEntry->getKey(),
                            'status' => 'categorized',
                            'updated_by' => Auth::id(),
                        ])->save();

                        Notification::make()
                            ->title('Journal entry created and linked')
                            ->success()
                            ->send();
                    }),
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

    protected static function getChartOfAccountOptions(): array
    {
        return ChartOfAccount::query()
            ->orderBy('code')
            ->get()
            ->mapWithKeys(fn (ChartOfAccount $account): array => [
                $account->getKey() => trim($account->code . ' - ' . $account->name),
            ])
            ->all();
    }
}