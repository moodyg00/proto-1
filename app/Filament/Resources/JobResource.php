<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobResource\Pages;
use App\Models\Booking;
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
                Forms\Components\DatePicker::make('scheduled_date')
                    ->disabled(fn (?Job $record): bool => $record?->booking()->exists() ?? false)
                    ->helperText(fn (?Job $record): ?string => ($record?->booking()->exists() ?? false)
                        ? 'Schedule date and time are owned by the booking record. Use Edit Booking.'
                        : null),
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
                Tables\Actions\Action::make('editBooking')
                    ->label(fn (Job $record): string => $record->booking()->exists() ? 'Edit Booking' : 'Create Booking')
                    ->icon('heroicon-o-calendar-days')
                    ->color('gray')
                    ->fillForm(function (Job $record): array {
                        $booking = $record->booking()->first();

                        return [
                            'booking_date' => optional($booking?->booking_date)->toDateString() ?: optional($record->scheduled_date)?->toDateString(),
                            'start_time' => optional($booking?->start_time)->format('H:i') ?: optional($record->booking_time)->format('H:i'),
                            'end_time' => optional($booking?->end_time)->format('H:i'),
                            'notes' => $booking?->notes,
                        ];
                    })
                    ->form([
                        Forms\Components\DatePicker::make('booking_date')
                            ->label('Booking Date')
                            ->required(),
                        Forms\Components\TimePicker::make('start_time')
                            ->label('Start Time')
                            ->seconds(false),
                        Forms\Components\TimePicker::make('end_time')
                            ->label('End Time')
                            ->seconds(false),
                        Forms\Components\Textarea::make('notes')
                            ->rows(3),
                    ])
                    ->action(function (Job $record, array $data): void {
                        $booking = Booking::query()->updateOrCreate(
                            ['work_order_id' => $record->getKey()],
                            [
                                'booking_date' => $data['booking_date'],
                                'start_time' => $data['start_time'] ?: null,
                                'end_time' => $data['end_time'] ?: null,
                                'address' => $record->address,
                                'notes' => $data['notes'] ?: null,
                                'updated_by' => auth()->id(),
                                'created_by' => auth()->id(),
                            ],
                        );

                        $record->forceFill([
                            'scheduled_date' => $booking->booking_date,
                            'booking_time' => $booking->start_time,
                            'status' => Job::normalizeStatus($record->status) === 'new' ? 'scheduled' : $record->status,
                            'updated_by' => auth()->id(),
                        ])->save();
                    })
                    ->successNotificationTitle('Booking saved'),
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
