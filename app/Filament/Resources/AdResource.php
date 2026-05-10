<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdResource\Pages;
use App\Models\Ad;
use App\Models\Campaign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AdResource extends Resource
{
    protected static ?string $model = Ad::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Marketing & Ads';

    protected static ?string $navigationLabel = 'Ads';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('platform')
                    ->options([
                        'facebook' => 'Facebook',
                        'instagram' => 'Instagram',
                        'google' => 'Google',
                        'youtube' => 'YouTube',
                        'linkedin' => 'LinkedIn',
                        'tiktok' => 'TikTok',
                        'other' => 'Other',
                    ])
                    ->required(),
                Forms\Components\Select::make('campaign_id')
                    ->label('Campaign')
                    ->options(fn (): array => Campaign::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('headline')
                    ->maxLength(255),
                Forms\Components\TextInput::make('hook')
                    ->maxLength(255),
                Forms\Components\TextInput::make('cta_text')
                    ->maxLength(120),
                Forms\Components\TextInput::make('cta_url')
                    ->url(),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'paused' => 'Paused',
                        'completed' => 'Completed',
                    ])
                    ->default('draft')
                    ->required(),
                Forms\Components\TextInput::make('budget')
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('performance_score')
                    ->numeric(),
                Forms\Components\TextInput::make('roas')
                    ->numeric(),
                Forms\Components\DatePicker::make('start_date'),
                Forms\Components\DatePicker::make('end_date'),
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('platform')->badge()->sortable(),
                Tables\Columns\TextColumn::make('campaign.name')->label('Campaign')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('headline')->limit(30)->toggleable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\TextColumn::make('budget')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('roas')->numeric(decimalPlaces: 2)->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('start_date')->date()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('end_date')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('platform')
                    ->options([
                        'facebook' => 'Facebook',
                        'instagram' => 'Instagram',
                        'google' => 'Google',
                        'youtube' => 'YouTube',
                        'linkedin' => 'LinkedIn',
                        'tiktok' => 'TikTok',
                        'other' => 'Other',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'paused' => 'Paused',
                        'completed' => 'Completed',
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
            'index' => Pages\ManageAds::route('/'),
        ];
    }
}
