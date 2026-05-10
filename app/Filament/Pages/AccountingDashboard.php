<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AccountingDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Accounting';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.accounting-dashboard';
}
