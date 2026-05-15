<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialMediaPostResource\Pages;
use App\Models\SocialMediaPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SocialMediaPostResource extends Resource
{
    protected static ?string $model = SocialMediaPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Content & Blog';

    protected static ?string $navigationLabel = 'Social Media';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Post Content')
                    ->schema([
                        Forms\Components\Select::make('platform')
                            ->options([
                                'facebook' => 'Facebook',
                                'instagram' => 'Instagram',
                                'linkedin' => 'LinkedIn',
                                'x' => 'X / Twitter',
                                'nextdoor' => 'Nextdoor',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('status')
                            ->options([
                                'new' => 'New',
                                'draft' => 'Draft',
                                'scheduled' => 'Scheduled',
                                'posted' => 'Posted',
                            ])
                            ->required()
                            ->default('new')
                            ->native(false),
                        Forms\Components\TextInput::make('image_id')
                            ->label('Asset Library ID')
                            ->helperText('Reference the chosen image or video asset id until the dedicated asset picker is wired in.'),
                        Forms\Components\TextInput::make('crop_url')
                            ->label('Platform Preview URL')
                            ->url()
                            ->helperText('Use a generated crop or preview URL for the selected platform visual.'),
                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Schedule Publish Time'),
                        Forms\Components\Textarea::make('caption')
                            ->rows(8)
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TagsInput::make('hashtags')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Platform Preview')
                    ->schema([
                        Forms\Components\Placeholder::make('preview_card')
                            ->label('Preview')
                            ->content(fn (?SocialMediaPost $record): string => filled($record?->caption)
                                ? str($record->caption)->limit(220)->toString()
                                : 'Save the post to preview its latest caption, platform, and scheduled publish information here.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('platform')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (?string $state): ?string => filled($state) ? str($state)->replace('_', ' ')->headline()->toString() : null),
                Tables\Columns\TextColumn::make('caption')
                    ->label('Caption')
                    ->limit(70)
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('platform')
                    ->options([
                        'facebook' => 'Facebook',
                        'instagram' => 'Instagram',
                        'linkedin' => 'LinkedIn',
                        'x' => 'X / Twitter',
                        'nextdoor' => 'Nextdoor',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'new' => 'New',
                        'draft' => 'Draft',
                        'scheduled' => 'Scheduled',
                        'posted' => 'Posted',
                    ]),
            ])
            ->recordUrl(fn (SocialMediaPost $record): string => static::getUrl('edit', ['record' => $record]))
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
            'index' => Pages\ManageSocialMediaPosts::route('/'),
            'edit' => Pages\EditSocialMediaPost::route('/{record}/edit'),
        ];
    }
}
