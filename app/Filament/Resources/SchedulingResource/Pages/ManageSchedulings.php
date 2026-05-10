<?php

namespace App\Filament\Resources\SchedulingResource\Pages;

use App\Filament\Resources\SchedulingResource;
use App\Models\Booking;
use App\Models\Job;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ManageSchedulings extends ManageRecords
{
    protected static string $resource = SchedulingResource::class;

    protected static string $view = 'filament.resources.scheduling-resource.pages.manage-schedulings';

    public function getHeading(): string | Htmlable
    {
        return '';
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->createBookingAction(),
        ];
    }

    public function createBookingAction(): Actions\Action
    {
        return Actions\Action::make('createBooking')
            ->label('Add Booking')
            ->icon('heroicon-m-plus')
            ->color('primary')
            ->modalHeading('Add Booking')
            ->modalSubmitActionLabel('Save booking')
            ->fillForm(function (array $arguments): array {
                $date = isset($arguments['date']) ? Carbon::parse($arguments['date'])->toDateString() : now()->toDateString();

                return [
                    'work_order_id' => $arguments['work_order_id'] ?? null,
                    'booking_date' => $date,
                    'start_time' => null,
                    'end_time' => null,
                    'notes' => null,
                ];
            })
            ->form($this->getBookingFormSchema())
            ->action(function (array $data): void {
                $job = Job::query()->findOrFail($data['work_order_id']);

                Booking::query()->updateOrCreate(
                    ['work_order_id' => $job->id],
                    [
                        'booking_date' => $data['booking_date'],
                        'start_time' => $data['start_time'] ?: null,
                        'end_time' => $data['end_time'] ?: null,
                        'address' => $job->address,
                        'notes' => $data['notes'] ?: null,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ],
                );

                $job->forceFill([
                    'scheduled_date' => $data['booking_date'],
                    'booking_date' => $data['booking_date'],
                    'booking_time' => $data['start_time'] ?: null,
                    'status' => $job->status === 'new' ? 'scheduled' : $job->status,
                    'updated_by' => Auth::id(),
                ])->save();

                Notification::make()
                    ->title('Booking added')
                    ->success()
                    ->send();

                $this->dispatch('schedule-bookings-updated');
            });
    }

    public function editBookingAction(): Actions\Action
    {
        return Actions\Action::make('editBooking')
            ->modalHeading('Edit Booking')
            ->modalSubmitActionLabel('Save changes')
            ->fillForm(function (array $arguments): array {
                $booking = Booking::query()->with('workOrder')->findOrFail($arguments['booking'] ?? null);

                return [
                    'booking_id' => $booking->id,
                    'work_order_id' => $booking->work_order_id,
                    'booking_date' => optional($booking->booking_date)->toDateString(),
                    'start_time' => optional($booking->start_time)->format('H:i'),
                    'end_time' => optional($booking->end_time)->format('H:i'),
                    'notes' => $booking->notes,
                ];
            })
            ->form($this->getBookingFormSchema(editing: true))
            ->action(function (array $data): void {
                $booking = Booking::query()->findOrFail($data['booking_id']);
                $job = Job::query()->findOrFail($booking->work_order_id);

                $booking->forceFill([
                    'booking_date' => $data['booking_date'],
                    'start_time' => $data['start_time'] ?: null,
                    'end_time' => $data['end_time'] ?: null,
                    'notes' => $data['notes'] ?: null,
                    'updated_by' => Auth::id(),
                ])->save();

                $job->forceFill([
                    'scheduled_date' => $data['booking_date'],
                    'booking_date' => $data['booking_date'],
                    'booking_time' => $data['start_time'] ?: null,
                    'updated_by' => Auth::id(),
                ])->save();

                Notification::make()
                    ->title('Booking updated')
                    ->success()
                    ->send();

                $this->dispatch('schedule-bookings-updated');
            });
    }

    protected function getBookingFormSchema(bool $editing = false): array
    {
        return [
            Forms\Components\Hidden::make('booking_id'),
            Forms\Components\Select::make('work_order_id')
                ->label('Work Order')
                ->options(fn (): array => Job::query()
                    ->whereNotIn('status', ['cancelled', 'archived'])
                    ->orderBy('work_order_number')
                    ->get()
                    ->mapWithKeys(fn (Job $job): array => [
                        $job->id => trim($job->work_order_number . ' - ' . ($job->customer_name ?: 'Unknown customer')),
                    ])
                    ->all())
                ->searchable()
                ->preload()
                ->disabled($editing)
                ->dehydrated(! $editing)
                ->required(),
            Forms\Components\DatePicker::make('booking_date')
                ->label('Booking Date')
                ->required(),
            Forms\Components\TimePicker::make('start_time')
                ->label('Start Time')
                ->seconds(false),
            Forms\Components\TimePicker::make('end_time')
                ->label('End Time')
                ->seconds(false),
            Forms\Components\Textarea::make('notes')
                ->label('Notes')
                ->rows(3),
        ];
    }

    public function getCalendarEvents(?string $start = null, ?string $end = null): array
    {
        $events = Booking::query()
            ->with('workOrder')
            ->when($start, fn ($query) => $query->whereDate('booking_date', '>=', Carbon::parse($start)->toDateString()))
            ->when($end, fn ($query) => $query->whereDate('booking_date', '<=', Carbon::parse($end)->toDateString()))
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get();

        return $events
            ->filter(fn (Booking $booking): bool => $booking->workOrder !== null)
            ->map(function (Booking $booking): array {
                $workOrder = $booking->workOrder;
                $start = Carbon::parse($booking->booking_date->toDateString() . ' ' . ($booking->start_time?->format('H:i:s') ?? '08:00:00'));
                $end = $booking->end_time
                    ? Carbon::parse($booking->booking_date->toDateString() . ' ' . $booking->end_time->format('H:i:s'))
                    : ($booking->start_time ? $start->copy()->addHour() : null);

                return [
                    'id' => $booking->id,
                    'title' => trim($workOrder->work_order_number . ' - ' . ($workOrder->customer_name ?: 'Unknown customer')),
                    'start' => $booking->start_time
                        ? $start->format('Y-m-d\TH:i:s')
                        : Carbon::parse($booking->booking_date)->format('Y-m-d'),
                    'end' => $end?->format('Y-m-d\TH:i:s'),
                    'allDay' => ! $booking->start_time,
                    'backgroundColor' => $this->getEventColor($workOrder->status),
                    'borderColor' => $this->getEventColor($workOrder->status),
                    'extendedProps' => [
                        'workOrderNumber' => $workOrder->work_order_number,
                        'customerName' => $workOrder->customer_name,
                        'service' => $workOrder->service_name,
                        'status' => Job::statusOptions()[Job::normalizeStatus($workOrder->status)] ?? str($workOrder->status)->headline()->toString(),
                    ],
                ];
            })
            ->values()
            ->all();
    }

    protected function getEventColor(?string $status): string
    {
        return match (Job::normalizeStatus($status)) {
            'scheduled' => '#0ea5e9',
            'in_progress' => '#f59e0b',
            'completed' => '#22c55e',
            'rework' => '#fb7185',
            default => '#64748b',
        };
    }
}
