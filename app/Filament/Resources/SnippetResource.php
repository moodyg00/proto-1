<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SnippetResource\Pages;
use App\Models\Snippet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SnippetResource extends Resource
{
    protected static ?string $model = Snippet::class;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    protected static ?string $navigationGroup = 'Integrations';

    protected static ?string $navigationLabel = 'Snippets';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('snippet_type')
                    ->options([
                        'javascript' => 'JavaScript',
                        'html' => 'HTML',
                        'css' => 'CSS',
                        'php' => 'PHP',
                        'other' => 'Other',
                    ])
                    ->required(),
                Forms\Components\Select::make('placement')
                    ->options([
                        'head' => 'Head',
                        'body_start' => 'Body Start',
                        'body_end' => 'Body End',
                        'footer' => 'Footer',
                        'specific_page' => 'Specific Page',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('page_slug')
                    ->maxLength(180),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->default('active')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('code')
                    ->required()
                    ->rows(12)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('snippet_type')->badge()->sortable(),
                Tables\Columns\TextColumn::make('placement')->badge()->sortable(),
                Tables\Columns\TextColumn::make('page_slug')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('snippet_type')
                    ->options([
                        'javascript' => 'JavaScript',
                        'html' => 'HTML',
                        'css' => 'CSS',
                        'php' => 'PHP',
                        'other' => 'Other',
                    ]),
                Tables\Filters\SelectFilter::make('placement')
                    ->options([
                        'head' => 'Head',
                        'body_start' => 'Body Start',
                        'body_end' => 'Body End',
                        'footer' => 'Footer',
                        'specific_page' => 'Specific Page',
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
            'index' => Pages\ManageSnippets::route('/'),
        ];
    }
}
