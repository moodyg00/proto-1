<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Tasks';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('category')
                    ->options([
                        'sales' => 'Sales',
                        'accounting' => 'Accounting',
                        'operations' => 'Operations',
                        'admin' => 'Administration',
                        'ai_task' => 'AI Task',
                        'other' => 'Other',
                    ])
                    ->default('other')
                    ->required(),
                Forms\Components\TextInput::make('type')
                    ->maxLength(120),
                Forms\Components\Select::make('assigned_to')
                    ->label('Assigned To')
                    ->options(fn (): array => User::query()->orderBy('full_name')->pluck('full_name', 'id')->all())
                    ->searchable()
                    ->preload()
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
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'failed' => 'Failed',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\DateTimePicker::make('due_date'),
                Forms\Components\Toggle::make('requires_human_approval')
                    ->default(false),
                Forms\Components\Select::make('instructions_format')
                    ->options([
                        'plain_text' => 'Plain Text',
                        'markdown' => 'Markdown',
                    ])
                    ->default('plain_text')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('instructions')
                    ->rows(4)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('related_type')
                    ->maxLength(80),
                Forms\Components\KeyValue::make('notes')
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('metadata')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category')->badge()->sortable(),
                Tables\Columns\TextColumn::make('type')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('assignedUser.full_name')->label('Assigned To')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('priority')->badge()->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('due_date')->dateTime()->sortable()->toggleable(),
                Tables\Columns\IconColumn::make('requires_human_approval')->boolean(),
                Tables\Columns\TextColumn::make('completed_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'sales' => 'Sales',
                        'accounting' => 'Accounting',
                        'operations' => 'Operations',
                        'admin' => 'Administration',
                        'ai_task' => 'AI Task',
                        'other' => 'Other',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'failed' => 'Failed',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
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
            'index' => Pages\ManageTasks::route('/'),
        ];
    }
}
