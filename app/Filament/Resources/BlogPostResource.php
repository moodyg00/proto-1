<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationGroup = 'Content & Blog';

    protected static ?string $navigationLabel = 'Blog';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(180),
                Forms\Components\TextInput::make('featured_image_url')
                    ->url(),
                Forms\Components\Select::make('author_id')
                    ->label('Author')
                    ->options(fn (): array => User::query()->orderBy('full_name')->pluck('full_name', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('category_id')
                    ->label('Category Record')
                    ->options(fn (): array => BlogCategory::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('category')
                    ->maxLength(120),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'scheduled' => 'Scheduled',
                        'archived' => 'Archived',
                    ])
                    ->default('draft')
                    ->required(),
                Forms\Components\DateTimePicker::make('published_at'),
                Forms\Components\TextInput::make('reading_time_minutes')
                    ->numeric()
                    ->minValue(1),
                Forms\Components\Textarea::make('excerpt')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('content')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('seo_title')
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('seo_description')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\TagsInput::make('seo_keywords')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('author.full_name')->label('Author')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category')->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('published_at')->dateTime()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('reading_time_minutes')->label('Read Time')->suffix(' min')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('view_count')->numeric()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'scheduled' => 'Scheduled',
                        'archived' => 'Archived',
                    ]),
            ])
            ->recordUrl(fn (BlogPost $record): string => static::getUrl('edit', ['record' => $record]))
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
            'index' => Pages\ManageBlogPosts::route('/'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
