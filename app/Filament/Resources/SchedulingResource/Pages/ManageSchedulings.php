<?php

namespace App\Filament\Resources\SchedulingResource\Pages;

use App\Filament\Resources\SchedulingResource;
use App\Filament\Widgets\SchedulingCalendarWidget;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ManageSchedulings extends Page
{
    protected static string $resource = SchedulingResource::class;

    protected static string $view = 'filament.resources.scheduling-resource.pages.manage-schedulings';

    public function getHeading(): string | Htmlable
    {
        return 'Schedule';
    }

    public function getSubheading(): ?string
    {
        return 'Click any day to add a booking. Switch between month, week, and day views to manage scheduled jobs.';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SchedulingCalendarWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }
}
