<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchedulingResource\Pages;
use App\Models\Scheduling;
use Filament\Resources\Resource;

class SchedulingResource extends Resource
{
    protected static ?string $model = Scheduling::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Schedule';

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return 'scheduled work order';
    }

    public static function getPluralModelLabel(): string
    {
        return 'scheduled work orders';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSchedulings::route('/'),
        ];
    }
}
