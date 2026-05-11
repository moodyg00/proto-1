<?php

namespace App\Filament\Resources\JobResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\JobResource;
use App\Models\Booking;
use App\Models\Job;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class ManageJobs extends AppLabManageRecords
{
    protected static string $resource = JobResource::class;

    protected static string $view = 'filament.resources.job-resource.pages.manage-jobs';

    public function getHeading(): string | Htmlable
    {
        return '';
    }

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
        ];
    }

    public function createWorkOrderAction(): Actions\CreateAction
    {
        return $this->makeCreateWorkOrderAction('createWorkOrder')
            ->label('Create Work Order')
            ->icon('heroicon-m-plus')
            ->color('primary');
    }

    public function quickCreateWorkOrderAction(): Actions\CreateAction
    {
        return $this->makeCreateWorkOrderAction('quickCreateWorkOrder')
            ->label('Add work order')
            ->icon('heroicon-m-plus')
            ->color('gray')
            ->iconButton();
    }

    public function editWorkOrderAction(): Actions\Action
    {
        return Actions\Action::make('editWorkOrder')
            ->modalHeading('Edit Work Order')
            ->modalSubmitActionLabel('Save changes')
            ->fillForm(function (array $arguments): array {
                $job = Job::query()->findOrFail($arguments['job'] ?? null);

                return [
                    'work_order_number' => $job->work_order_number,
                    'invoice_number' => $job->invoice_number,
                    'customer_name' => $job->customer_name,
                    'service_name' => $job->service_name,
                    'assigned_contractor' => $job->assigned_contractor,
                    'contractor_status' => $job->contractor_status,
                    'status' => Job::normalizeStatus($job->status),
                    'scheduled_date' => $job->scheduled_date,
                    'address' => $job->address,
                    'special_instructions' => $job->special_instructions,
                    'notes' => $job->notes,
                ];
            })
            ->form(fn (Form $form): array => JobResource::form($form)->getComponents())
            ->action(function (array $data, array $arguments): void {
                $job = Job::query()->findOrFail($arguments['job'] ?? null);

                if ($job->booking()->exists()) {
                    unset($data['scheduled_date']);
                    unset($data['booking_time']);
                }

                $job->fill($data);
                $job->status = Job::normalizeStatus($job->status);
                $job->updated_by = Auth::id();

                if ($job->status === 'completed' && ! $job->completed_at) {
                    $job->completed_at = now();
                }

                if ($job->status !== 'completed') {
                    $job->completed_at = null;
                }

                $job->save();

                Notification::make()
                    ->title('Work order updated')
                    ->success()
                    ->send();

                $this->resetTable();
            });
    }

    public function editBookingAction(): Actions\Action
    {
        return Actions\Action::make('editBooking')
            ->modalHeading(fn (array $arguments): string => Job::query()->findOrFail($arguments['job'] ?? null)->booking()->exists() ? 'Edit Booking' : 'Create Booking')
            ->modalSubmitActionLabel('Save booking')
            ->fillForm(function (array $arguments): array {
                $job = Job::query()->findOrFail($arguments['job'] ?? null);
                $booking = $job->booking()->first();

                return [
                    'booking_date' => optional($booking?->booking_date)->toDateString() ?: optional($job->scheduled_date)?->toDateString(),
                    'start_time' => optional($booking?->start_time)->format('H:i') ?: optional($job->booking_time)->format('H:i'),
                    'end_time' => optional($booking?->end_time)->format('H:i'),
                    'notes' => $booking?->notes,
                ];
            })
            ->form([
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
                    ->rows(3),
            ])
            ->action(function (array $data, array $arguments): void {
                $job = Job::query()->findOrFail($arguments['job'] ?? null);

                $booking = Booking::query()->updateOrCreate(
                    ['work_order_id' => $job->getKey()],
                    [
                        'booking_date' => $data['booking_date'],
                        'start_time' => $data['start_time'] ?: null,
                        'end_time' => $data['end_time'] ?: null,
                        'address' => $job->address,
                        'notes' => $data['notes'] ?: null,
                        'updated_by' => Auth::id(),
                        'created_by' => Auth::id(),
                    ],
                );

                $job->forceFill([
                    'scheduled_date' => $booking->booking_date,
                    'booking_time' => $booking->start_time,
                    'status' => Job::normalizeStatus($job->status) === 'new' ? 'scheduled' : $job->status,
                    'updated_by' => Auth::id(),
                ])->save();

                Notification::make()
                    ->title('Booking saved')
                    ->success()
                    ->send();

                $this->resetTable();
            });
    }

    protected function makeCreateWorkOrderAction(string $name): Actions\CreateAction
    {
        return Actions\CreateAction::make($name)
            ->model(Job::class)
            ->createAnother(false)
            ->modalHeading('Create Work Order')
            ->modalSubmitActionLabel('Create Work Order')
            ->form(fn (Form $form): array => JobResource::form($form)->getComponents())
            ->fillForm(fn (array $arguments): array => [
                'status' => Job::normalizeStatus($arguments['status'] ?? 'new'),
            ])
            ->mutateFormDataUsing(function (array $data, array $arguments): array {
                $data['status'] = Job::normalizeStatus($arguments['status'] ?? ($data['status'] ?? 'new'));
                $data['updated_by'] = Auth::id();
                $data['created_by'] = Auth::id();

                if (($data['status'] ?? null) === 'completed' && blank($data['completed_at'] ?? null)) {
                    $data['completed_at'] = now();
                }

                return $data;
            })
            ->successNotificationTitle('Work order created')
            ->after(fn () => $this->resetTable());
    }

    public function isTableView(): bool
    {
        return $this->isListView();
    }

    protected function hasKanbanView(): bool
    {
        return true;
    }

    protected function getDefaultViewType(): string
    {
        return 'kanban';
    }

    public function getWorkOrderKanbanColumns(): array
    {
        $jobs = Job::query()
            ->orderByRaw("CASE WHEN status = 'assigned' THEN 'scheduled' ELSE status END")
            ->orderByDesc('updated_at')
            ->get()
            ->map(function (Job $job): Job {
                $job->status = Job::normalizeStatus($job->status);

                return $job;
            })
            ->groupBy('status');

        return collect(Job::kanbanStatuses())
            ->map(function (array $meta, string $status) use ($jobs): array {
                /** @var Collection<int, Job> $columnJobs */
                $columnJobs = $jobs->get($status, new Collection());

                return [
                    'key' => $status,
                    'label' => $meta['label'],
                    'description' => $meta['description'],
                    'accent' => $meta['accent'],
                    'job_count' => $columnJobs->count(),
                    'jobs' => $columnJobs,
                ];
            })
            ->values()
            ->all();
    }

    public function requestJobStatusChange(string $jobId, string $status): void
    {
        if (! array_key_exists($status, Job::kanbanStatuses())) {
            return;
        }

        $job = Job::query()->find($jobId);

        if (! $job) {
            return;
        }

        $job->status = $status;
        $job->updated_by = Auth::id();

        if ($status === 'completed' && ! $job->completed_at) {
            $job->completed_at = now();
        }

        if ($status !== 'completed') {
            $job->completed_at = null;
        }

        $job->save();

        $this->resetTable();
    }

    public function getJobColumnClasses(string $accent): string
    {
        return match ($accent) {
            'blue' => 'ring-1 ring-sky-200/80 dark:ring-sky-900/60',
            'amber' => 'ring-1 ring-amber-200/80 dark:ring-amber-900/60',
            'emerald' => 'ring-1 ring-emerald-200/80 dark:ring-emerald-900/60',
            'rose' => 'ring-1 ring-rose-200/80 dark:ring-rose-900/60',
            default => 'ring-1 ring-slate-200/90 dark:ring-slate-800/80',
        };
    }

    public function getJobCardPills(Job $job): array
    {
        $pills = [];

        if (filled($job->service_name)) {
            $pills[] = [
                'icon' => 'heroicon-m-wrench-screwdriver',
                'label' => $job->service_name,
            ];
        }

        if ($job->scheduled_date) {
            $pills[] = [
                'icon' => 'heroicon-m-calendar-days',
                'label' => $job->scheduled_date->format('M j'),
            ];
        }

        if (filled($job->assigned_contractor)) {
            $pills[] = [
                'icon' => 'heroicon-m-user',
                'label' => $job->assigned_contractor,
            ];
        }

        return $pills;
    }
}
