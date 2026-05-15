<?php

namespace App\Filament\Resources\SchedulingResource\Pages;

use App\Filament\Resources\SchedulingResource;
use App\Filament\Widgets\SchedulingCalendarWidget;
use App\Models\Scheduling;
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

    public function getBookingTypeOptions(): array
    {
        return Scheduling::query()
            ->whereNotNull('service_name')
            ->orderBy('service_name')
            ->get(['service_id', 'service_name'])
            ->unique(fn (Scheduling $scheduling): string => (string) ($scheduling->service_id ?: $scheduling->service_name))
            ->mapWithKeys(fn (Scheduling $scheduling): array => [
                (string) ($scheduling->service_id ?: $scheduling->service_name) => $scheduling->service_name,
            ])
            ->all();
    }

    public function getAvailabilityScopeOptions(): array
    {
        return [
            'all' => 'All availability',
            'admin' => 'Admin availability',
            'contractor' => 'Contractor availability',
            'public' => 'Public booking availability',
        ];
    }
}
