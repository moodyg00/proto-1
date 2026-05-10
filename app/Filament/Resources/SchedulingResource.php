<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchedulingResource\Pages;
use App\Models\Scheduling;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SchedulingResource extends Resource
{
    protected static ?string $model = Scheduling::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Schedule';

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return 'scheduled work order';
    }

    public static function getPluralModelLabel(): string
    {
        return 'scheduled work orders';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('work_order_number')
                    ->label('Work Order #')
                    ->required()
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
                Forms\Components\Select::make('status')
                    ->options(Scheduling::statusOptions())
                    ->default('scheduled')
                    ->required(),
                Forms\Components\DatePicker::make('scheduled_date')
                    ->required(),
                Forms\Components\DatePicker::make('booking_date'),
                Forms\Components\TimePicker::make('booking_time')->seconds(false),
                Forms\Components\KeyValue::make('address')->columnSpanFull(),
                Forms\Components\Textarea::make('special_instructions')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('scheduled_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('booking_time')->time('H:i')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('work_order_number')->label('Work Order #')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('customer_name')->label('Customer')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('service_name')->label('Service')->toggleable(),
                Tables\Columns\TextColumn::make('assigned_contractor')->label('Assigned Contractor')->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('booking_date')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(Scheduling::statusOptions()),
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
            ->whereNotNull('scheduled_date')
            ->orderBy('scheduled_date');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSchedulings::route('/'),
        ];
    }
}
