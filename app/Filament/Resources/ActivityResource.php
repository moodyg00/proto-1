<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Models\Activity;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Organization;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Customer Relations';

    protected static ?string $navigationLabel = 'Activities';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->required()->maxLength(255),
                Forms\Components\Textarea::make('description')->rows(3),
                Forms\Components\TextInput::make('type')->required()->maxLength(80),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'scheduled' => 'Scheduled',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\Select::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ])
                    ->default('medium')
                    ->required(),
                Forms\Components\DateTimePicker::make('activity_date'),
                Forms\Components\DateTimePicker::make('due_at'),
                Forms\Components\Select::make('assigned_to')->label('Assigned To')
                    ->options(fn () => User::query()->pluck('full_name', 'id'))
                    ->searchable()->nullable(),
                Forms\Components\Select::make('lead_id')->label('Lead')
                    ->options(fn () => Lead::query()->pluck('name', 'id'))
                    ->searchable()->nullable(),
                Forms\Components\Select::make('contact_id')->label('Contact')
                    ->options(fn () => Contact::query()->pluck('name', 'id'))
                    ->searchable()->nullable(),
                Forms\Components\Select::make('organization_id')->label('Organization')
                    ->options(fn () => Organization::query()->pluck('name', 'id'))
                    ->searchable()->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('priority')->badge()->sortable(),
                Tables\Columns\TextColumn::make('lead.name')->label('Lead')->toggleable(),
                Tables\Columns\TextColumn::make('contact.name')->label('Contact')->toggleable(),
                Tables\Columns\TextColumn::make('organization.name')->label('Organization')->toggleable(),
                Tables\Columns\TextColumn::make('activity_date')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('due_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('assignedUser.full_name')->label('Assigned To')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'scheduled' => 'Scheduled',
                    'completed' => 'Completed',
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
            'index' => Pages\ManageActivities::route('/'),
        ];
    }
}