<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('module')
                    ->options(static::getModuleOptions())
                    ->required()
                    ->searchable()
                    ->native(false),
                Forms\Components\TextInput::make('key')
                    ->required()
                    ->maxLength(120),
                Forms\Components\Toggle::make('is_sensitive')
                    ->default(false),
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('value')
                    ->rows(8)
                    ->formatStateUsing(fn (mixed $state): string => static::formatSettingValue($state))
                    ->dehydrateStateUsing(fn (?string $state): mixed => static::parseSettingValue($state))
                    ->helperText('Plain text is saved as a string. Valid JSON is preserved as structured data.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('module')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => static::getModuleLabel($state)),
                Tables\Columns\TextColumn::make('key')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->toggleable()
                    ->wrap()
                    ->formatStateUsing(fn (mixed $state): string => Str::limit(static::formatSettingValue($state), 80)),
                Tables\Columns\IconColumn::make('is_sensitive')->boolean(),
                Tables\Columns\TextColumn::make('description')->limit(50)->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('module')
                    ->options(static::getModuleOptions()),
                Tables\Filters\SelectFilter::make('category')
                    ->options(static::getCategoryOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (! $value || ! array_key_exists($value, static::getCategoryModules())) {
                            return $query;
                        }

                        return $query->whereIn('module', static::getCategoryModules()[$value]);
                    }),
                Tables\Filters\TernaryFilter::make('is_sensitive'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('module');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSettings::route('/'),
        ];
    }

    public static function getModuleOptions(): array
    {
        return [
            'business' => 'Business',
            'accounting' => 'Accounting',
            'operations' => 'Operations',
            'crm' => 'Customer Relations',
            'customer_relations' => 'Customer Relations',
            'ui_preferences' => 'User Preferences',
            'user_preferences' => 'User Preferences',
        ];
    }

    public static function getCategoryOptions(): array
    {
        return [
            'business' => 'Business Settings',
            'operations' => 'Operations Settings',
            'customer_relations' => 'Customer Relations Settings',
            'user_preferences' => 'User Preferences',
        ];
    }

    public static function getCategoryModules(): array
    {
        return [
            'business' => ['business', 'accounting'],
            'operations' => ['operations'],
            'customer_relations' => ['crm', 'customer_relations'],
            'user_preferences' => ['ui_preferences', 'user_preferences'],
        ];
    }

    public static function getModuleLabel(string $module): string
    {
        return static::getModuleOptions()[$module] ?? Str::headline($module);
    }

    public static function formatSettingValue(mixed $state): string
    {
        if ($state === null) {
            return '';
        }

        if (is_string($state)) {
            return $state;
        }

        return json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '';
    }

    public static function parseSettingValue(?string $state): mixed
    {
        $state = trim((string) $state);

        if ($state === '') {
            return null;
        }

        $decoded = json_decode($state, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $state;
    }
}
