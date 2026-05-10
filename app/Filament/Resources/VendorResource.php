<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorResource\Pages;
use App\Models\Organization;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VendorResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Accounting';

    protected static ?string $navigationLabel = 'Vendors';

    protected static ?int $navigationSort = 10;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereIn('relationship_type', ['vendor', 'contractor', 'affiliate', 'supplier']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\Select::make('relationship_type')
                    ->options([
                        'vendor' => 'Vendor',
                        'contractor' => 'Contractor',
                        'affiliate' => 'Affiliate',
                        'supplier' => 'Supplier',
                    ])
                    ->default('vendor')
                    ->required(),
                Forms\Components\TextInput::make('industry')->maxLength(120),
                Forms\Components\TextInput::make('phone')->tel()->maxLength(40),
                Forms\Components\TextInput::make('website')->url(),
                Forms\Components\TextInput::make('tax_id')->maxLength(255),
                Forms\Components\Toggle::make('is_1099_vendor')->default(false),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'pending' => 'Pending',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('relationship_type')->badge()->sortable(),
                Tables\Columns\TextColumn::make('phone')->toggleable(),
                Tables\Columns\IconColumn::make('is_1099_vendor')->boolean()->label('1099'),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
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
            'index' => Pages\ManageVendors::route('/'),
        ];
    }
}