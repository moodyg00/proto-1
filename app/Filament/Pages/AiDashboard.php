<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AiDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'AI Tools';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.ai-dashboard';
}
