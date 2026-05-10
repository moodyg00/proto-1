<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobResource\Pages;
use App\Models\Job;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JobResource extends Resource
{
    protected static ?string $model = Job::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Work Orders';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return 'work order';
    }

    public static function getPluralModelLabel(): string
    {
        return 'work orders';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('work_order_number')
                    ->label('Work Order #')
                    ->required()
                    ->maxLength(40),
                Forms\Components\TextInput::make('invoice_number')
                    ->label('Invoice #')
                    ->maxLength(40),
                Forms\Components\TextInput::make('customer_name')
                    ->label('Customer')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('service_name')
                    ->label('Service')
                    ->maxLength(255),
                Forms\Components\TextInput::make('assigned_contractor')
                    ->label('Assigned Contractor')
                    ->maxLength(255),
                Forms\Components\Select::make('contractor_status')
                    ->options([
                        'pending' => 'Pending',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options(Job::statusOptions())
                    ->default('scheduled')
                    ->required(),
                Forms\Components\DatePicker::make('scheduled_date'),
                Forms\Components\DatePicker::make('booking_date'),
                Forms\Components\TimePicker::make('booking_time')->seconds(false),
                Forms\Components\KeyValue::make('address')->columnSpanFull(),
                Forms\Components\Textarea::make('special_instructions')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('notes')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('work_order_number')->label('Work Order #')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('customer_name')->label('Customer')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('service_name')->label('Service')->toggleable(),
                Tables\Columns\TextColumn::make('assigned_contractor')->label('Assigned Contractor')->toggleable(),
                Tables\Columns\TextColumn::make('contractor_status')->badge()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('scheduled_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('booking_date')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('completed_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(Job::statusOptions()),
                Tables\Filters\SelectFilter::make('contractor_status')
                    ->options([
                        'pending' => 'Pending',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
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
            'index' => Pages\ManageJobs::route('/'),
        ];
    }
}
