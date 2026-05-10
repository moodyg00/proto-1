<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Customer Relations';

    protected static ?string $navigationLabel = 'Leads';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),

                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(50),

                Forms\Components\TextInput::make('title')
                    ->maxLength(255),

                Forms\Components\Select::make('source')
                    ->options(Lead::sourceOptions())
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('status')
                    ->options([
                        'uncontacted' => 'Uncontacted',
                        'contacted'   => 'Contacted',
                        'quoted'      => 'Quoted',
                        'booked'      => 'Booked',
                        'converted'   => 'Converted',
                        'lost'        => 'Lost',
                    ])
                    ->default('uncontacted')
                    ->required(),

                Forms\Components\Select::make('assigned_to')
                    ->label('Assigned To')
                    ->options(fn () => User::query()->pluck('full_name', 'id'))
                    ->searchable()
                    ->nullable(),

                Forms\Components\TextInput::make('expected_value')
                    ->numeric()
                    ->prefix('$'),

                Forms\Components\DateTimePicker::make('next_follow_up'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('phone')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('title')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'uncontacted',
                        'primary'   => 'contacted',
                        'warning'   => 'quoted',
                        'success'   => ['booked', 'converted'],
                        'danger'    => 'lost',
                    ]),

                Tables\Columns\TextColumn::make('source')
                    ->formatStateUsing(fn (?string $state): ?string => filled($state) ? str($state)->replace('_', ' ')->headline()->toString() : null)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('assignedUser.full_name')
                    ->label('Assigned To')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('expected_value')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('next_follow_up')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'uncontacted' => 'Uncontacted',
                        'contacted'   => 'Contacted',
                        'quoted'      => 'Quoted',
                        'booked'      => 'Booked',
                        'converted'   => 'Converted',
                        'lost'        => 'Lost',
                    ]),
                Tables\Filters\SelectFilter::make('source')
                    ->options(Lead::sourceOptions()),
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
            'index' => Pages\ManageLeads::route('/'),
        ];
    }
}

