<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Job;
use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class SchedulingCalendarWidget extends FullCalendarWidget
{
    protected static bool $isLazy = false;

    public Model | string | null $model = Booking::class;

    public function config(): array
    {
        return [
            'initialView' => 'dayGridMonth',
            'height' => 'auto',
            'dayMaxEvents' => true,
            'stickyHeaderDates' => false,
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,timeGridDay',
            ],
            'buttonText' => [
                'today' => 'today',
                'month' => 'month',
                'week' => 'week',
                'day' => 'day',
            ],
            'eventTimeFormat' => [
                'hour' => 'numeric',
                'minute' => '2-digit',
                'meridiem' => 'short',
            ],
        ];
    }

    public function fetchEvents(array $info): array
    {
        return Booking::query()
            ->with('workOrder')
            ->whereDate('booking_date', '>=', Carbon::parse($info['start'])->toDateString())
            ->whereDate('booking_date', '<=', Carbon::parse($info['end'])->toDateString())
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->get()
            ->filter(fn (Booking $booking): bool => $booking->workOrder !== null)
            ->map(function (Booking $booking): array {
                $workOrder = $booking->workOrder;
                $start = Carbon::parse($booking->booking_date->toDateString() . ' ' . ($booking->start_time?->format('H:i:s') ?? '08:00:00'));
                $end = $booking->end_time
                    ? Carbon::parse($booking->booking_date->toDateString() . ' ' . $booking->end_time->format('H:i:s'))
                    : ($booking->start_time ? $start->copy()->addHour() : null);

                return [
                    'id' => $booking->getKey(),
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

    public function getFormSchema(): array
    {
        return [
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
                ->required()
                ->disabled(fn (string $operation): bool => in_array($operation, ['edit', 'view'], true))
                ->dehydrated(fn (string $operation): bool => $operation === 'create'),
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

    protected function headerActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add Booking')
                ->icon('heroicon-m-plus')
                ->color('primary')
                ->createAnother(false)
                ->modalHeading('Add Booking')
                ->modalSubmitActionLabel('Save booking')
                ->successNotificationTitle('Booking added')
                ->mountUsing(function (Forms\Form $form, array $arguments): void {
                    $form->fill($this->getSelectionFormData($arguments));
                })
                ->using(function (array $data): Booking {
                    $job = Job::query()->findOrFail($data['work_order_id']);

                    $booking = Booking::query()->updateOrCreate(
                        ['work_order_id' => $job->getKey()],
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

                    $this->syncJobFromBookingData($job, $data);

                    return $booking;
                }),
        ];
    }

    protected function modalActions(): array
    {
        return [
            Actions\EditAction::make()
                ->modalHeading('Edit Booking')
                ->modalSubmitActionLabel('Save changes')
                ->successNotificationTitle('Booking updated')
                ->fillForm(fn (Booking $record): array => $this->getRecordFormData($record))
                ->using(function (Booking $record, array $data): Booking {
                    $record->forceFill([
                        'booking_date' => $data['booking_date'],
                        'start_time' => $data['start_time'] ?: null,
                        'end_time' => $data['end_time'] ?: null,
                        'notes' => $data['notes'] ?: null,
                        'updated_by' => Auth::id(),
                    ])->save();

                    $job = Job::query()->findOrFail($record->work_order_id);
                    $this->syncJobFromBookingData($job, $data);

                    return $record;
                }),
            Actions\DeleteAction::make()
                ->successNotificationTitle('Booking deleted')
                ->using(function (Booking $record): bool {
                    $job = Job::query()->find($record->work_order_id);

                    $deleted = (bool) $record->delete();

                    if ($job) {
                        $job->forceFill([
                            'scheduled_date' => null,
                            'booking_time' => null,
                            'status' => Job::normalizeStatus($job->status) === 'scheduled' ? 'new' : $job->status,
                            'updated_by' => Auth::id(),
                        ])->save();
                    }

                    return $deleted;
                }),
        ];
    }

    protected function viewAction(): \Filament\Actions\Action
    {
        return Actions\ViewAction::make()
            ->modalHeading('Booking Details')
            ->fillForm(fn (Booking $record): array => $this->getRecordFormData($record));
    }

    public function eventDidMount(): string
    {
        return <<<'JS'
            function({ event, timeText, el, view }) {
                if (view.type !== 'dayGridMonth') {
                    return;
                }

                const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;',
                }[char]));

                const workOrderNumber = event.extendedProps.workOrderNumber || event.title;
                const isMobile = window.matchMedia('(max-width: 640px)').matches;

                el.classList.add('schedule-calendar__month-event-host');
                el.setAttribute('title', [timeText, workOrderNumber].filter(Boolean).join(' '));

                if (isMobile) {
                    el.classList.add('schedule-calendar__month-event-host--mobile');
                    el.innerHTML = '<span class="schedule-calendar__dot" aria-hidden="true"></span><span class="sr-only">' + escapeHtml(workOrderNumber) + '</span>';
                    return;
                }

                el.innerHTML = '<span class="schedule-calendar__month-event"><span class="schedule-calendar__month-time">' + escapeHtml(timeText) + '</span><span class="schedule-calendar__month-label">' + escapeHtml(workOrderNumber) + '</span></span>';
            }
        JS;
    }

    protected function getSelectionFormData(array $arguments): array
    {
        $start = isset($arguments['start']) ? Carbon::parse($arguments['start']) : now();
        $end = isset($arguments['end']) && filled($arguments['end']) ? Carbon::parse($arguments['end']) : null;
        $allDay = (bool) ($arguments['allDay'] ?? false);

        return [
            'work_order_id' => null,
            'booking_date' => $start->toDateString(),
            'start_time' => $allDay ? null : $start->format('H:i'),
            'end_time' => ($allDay || ! $end) ? null : $end->format('H:i'),
            'notes' => null,
        ];
    }

    protected function getRecordFormData(Booking $booking): array
    {
        return [
            'work_order_id' => $booking->work_order_id,
            'booking_date' => optional($booking->booking_date)->toDateString(),
            'start_time' => optional($booking->start_time)->format('H:i'),
            'end_time' => optional($booking->end_time)->format('H:i'),
            'notes' => $booking->notes,
        ];
    }

    protected function syncJobFromBookingData(Job $job, array $data): void
    {
        $job->forceFill([
            'scheduled_date' => $data['booking_date'],
            'booking_time' => $data['start_time'] ?: null,
            'status' => Job::normalizeStatus($job->status) === 'new' ? 'scheduled' : $job->status,
            'updated_by' => Auth::id(),
        ])->save();
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