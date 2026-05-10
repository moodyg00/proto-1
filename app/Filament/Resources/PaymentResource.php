<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Bill;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Accounting';

    protected static ?string $navigationLabel = 'Payments';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('payment_number')
                    ->required()
                    ->maxLength(40),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                Forms\Components\DatePicker::make('payment_date')
                    ->required(),
                Forms\Components\Select::make('method')
                    ->options([
                        'cash' => 'Cash',
                        'check' => 'Check',
                        'credit_card' => 'Credit Card',
                        'bank_transfer' => 'Bank Transfer',
                        'other' => 'Other',
                    ])
                    ->required(),
                Forms\Components\Select::make('payment_direction')
                    ->options([
                        'incoming' => 'Incoming',
                        'outgoing' => 'Outgoing',
                    ])
                    ->default('incoming')
                    ->required(),
                Forms\Components\Select::make('reconciliation_status')
                    ->options([
                        'pending' => 'Pending',
                        'reconciled' => 'Reconciled',
                        'failed' => 'Failed',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\TextInput::make('reference')
                    ->maxLength(120),
                Forms\Components\Select::make('invoice_id')
                    ->label('Invoice')
                    ->options(fn (): array => Invoice::query()->orderBy('invoice_number')->pluck('invoice_number', 'id')->all())
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('bill_id')
                    ->label('Bill')
                    ->options(fn (): array => Bill::query()->orderBy('bill_number')->pluck('bill_number', 'id')->all())
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('contact_id')
                    ->label('Contact')
                    ->options(fn (): array => Contact::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('organization_id')
                    ->label('Organization')
                    ->options(fn (): array => Organization::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload(),
                Forms\Components\Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_number')->label('Payment #')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('amount')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('payment_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('method')->badge()->sortable(),
                Tables\Columns\TextColumn::make('payment_direction')->badge()->sortable(),
                Tables\Columns\TextColumn::make('reconciliation_status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('invoice.invoice_number')->label('Invoice')->toggleable(),
                Tables\Columns\TextColumn::make('bill.bill_number')->label('Bill')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('contact.name')->label('Contact')->toggleable(),
                Tables\Columns\TextColumn::make('organization.name')->label('Organization')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('method')
                    ->options([
                        'cash' => 'Cash',
                        'check' => 'Check',
                        'credit_card' => 'Credit Card',
                        'bank_transfer' => 'Bank Transfer',
                        'other' => 'Other',
                    ]),
                Tables\Filters\SelectFilter::make('payment_direction')
                    ->options([
                        'incoming' => 'Incoming',
                        'outgoing' => 'Outgoing',
                    ]),
                Tables\Filters\SelectFilter::make('reconciliation_status')
                    ->options([
                        'pending' => 'Pending',
                        'reconciled' => 'Reconciled',
                        'failed' => 'Failed',
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
            'index' => Pages\ManagePayments::route('/'),
        ];
    }
}
