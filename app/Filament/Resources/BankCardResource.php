<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankCardResource\Pages;
use App\Models\BankAccount;
use App\Models\BankCard;
use App\Models\Organization;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BankCardResource extends Resource
{
    protected static ?string $model = BankCard::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Banking';

    protected static ?string $navigationLabel = 'Cards';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('card_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('last4')
                    ->label('Last 4')
                    ->maxLength(4),
                Forms\Components\TextInput::make('mercury_card_id')
                    ->maxLength(120),
                Forms\Components\Select::make('vendor_id')
                    ->label('Vendor')
                    ->options(fn (): array => Organization::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('bank_account_id')
                    ->label('Account')
                    ->options(fn (): array => BankAccount::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('daily_limit')
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('per_transaction_limit')
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'paused' => 'Paused',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('card_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('last4')->label('Last 4')->sortable(),
                Tables\Columns\TextColumn::make('bankAccount.name')->label('Account')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('vendor.name')->label('Vendor')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('daily_limit')->money('USD')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('per_transaction_limit')->money('USD')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'paused' => 'Paused',
                        'cancelled' => 'Cancelled',
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
            'index' => Pages\ManageBankCards::route('/'),
        ];
    }
}