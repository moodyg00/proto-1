<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankAccountResource\Pages;
use App\Models\BankAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'Banking';

    protected static ?string $navigationLabel = 'Accounts';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('account_type')
                    ->options([
                        'checking' => 'Checking',
                        'savings' => 'Savings',
                        'cash' => 'Cash',
                        'credit_card' => 'Credit Card',
                        'other' => 'Other',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('bank_name')
                    ->maxLength(120),
                Forms\Components\TextInput::make('account_number')
                    ->maxLength(120),
                Forms\Components\TextInput::make('currency')
                    ->default('USD')
                    ->required()
                    ->maxLength(10),
                Forms\Components\TextInput::make('current_balance')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                Forms\Components\DatePicker::make('last_reconciled_date'),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('account_type')->badge()->sortable(),
                Tables\Columns\TextColumn::make('bank_name')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('account_number')->label('Account #')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('currency')->sortable(),
                Tables\Columns\TextColumn::make('current_balance')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('last_reconciled_date')->date()->sortable()->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('account_type')
                    ->options([
                        'checking' => 'Checking',
                        'savings' => 'Savings',
                        'cash' => 'Cash',
                        'credit_card' => 'Credit Card',
                        'other' => 'Other',
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
            'index' => Pages\ManageBankAccounts::route('/'),
        ];
    }
}
