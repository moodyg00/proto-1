<?php

namespace App\Filament\Resources\LeadResource\Pages;

use App\Filament\Resources\Pages\AppLabManageRecords;
use App\Filament\Resources\LeadResource;
use App\Models\Lead;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class ManageLeads extends AppLabManageRecords
{
    protected static string $resource = LeadResource::class;

    protected static string $view = 'filament.resources.lead-resource.pages.manage-leads';

    protected static array $leadBoardStatuses = [
        'uncontacted' => [
            'label' => 'New',
            'description' => 'Create a lead to organize your goals.',
            'accent' => 'slate',
        ],
        'contacted' => [
            'label' => 'Follow Up',
            'description' => 'Create a lead to organize your goals.',
            'accent' => 'blue',
        ],
        'quoted' => [
            'label' => 'Prospect',
            'description' => 'Create a lead to organize your goals.',
            'accent' => 'amber',
        ],
        'booked' => [
            'label' => 'Negotiation',
            'description' => 'Create a lead to organize your goals.',
            'accent' => 'emerald',
        ],
        'converted' => [
            'label' => 'Won',
            'description' => 'Create a lead to organize your goals.',
            'accent' => 'teal',
        ],
        'lost' => [
            'label' => 'Lost',
            'description' => 'Create a lead to organize your goals.',
            'accent' => 'rose',
        ],
    ];

    public string $pipelineId = 'default';

    public ?string $pendingLeadId = null;

    public ?string $pendingLeadStatus = null;

    public ?string $pendingLeadClosedAt = null;

    public ?string $pendingLeadLostReason = null;

    public ?string $pendingLeadValue = null;

    public function mount(): void
    {
        $this->pipelineId = (string) request()->query('pipeline_id', 'default');

        parent::mount();
    }

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

    public function createLeadAction(): Actions\CreateAction
    {
        return $this->makeCreateLeadAction('createLead')
            ->label('Create Lead')
            ->icon('heroicon-m-plus')
            ->color('primary');
    }

    public function quickCreateLeadAction(): Actions\CreateAction
    {
        return $this->makeCreateLeadAction('quickCreateLead')
            ->label('Add lead')
            ->icon('heroicon-m-plus')
            ->color('gray')
            ->iconButton();
    }

    public function editLeadAction(): Actions\Action
    {
        return Actions\Action::make('editLead')
            ->modalHeading('Edit Lead')
            ->modalSubmitActionLabel('Save changes')
            ->fillForm(function (array $arguments): array {
                $lead = Lead::query()->findOrFail($arguments['lead'] ?? null);

                return [
                    'name' => $lead->name,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'title' => $lead->title,
                    'source' => $lead->source,
                    'status' => $lead->status,
                    'assigned_to' => $lead->assigned_to,
                    'expected_value' => $lead->expected_value,
                    'next_follow_up' => $lead->next_follow_up,
                ];
            })
            ->form(fn (Form $form): array => LeadResource::form($form)->getComponents())
            ->action(function (array $data, array $arguments): void {
                $lead = Lead::query()->findOrFail($arguments['lead'] ?? null);

                $lead->fill($data);
                $lead->updated_by = Auth::id();
                $lead->save();

                Notification::make()
                    ->title('Lead updated')
                    ->success()
                    ->send();

                $this->resetTable();
            });
    }

    protected function makeCreateLeadAction(string $name): Actions\CreateAction
    {
        return Actions\CreateAction::make($name)
            ->model(Lead::class)
            ->createAnother(false)
            ->modalHeading('Create Lead')
            ->modalSubmitActionLabel('Create Lead')
            ->form(fn (Form $form): array => LeadResource::form($form)->getComponents())
            ->fillForm(fn (array $arguments): array => [
                'status' => $arguments['status'] ?? 'uncontacted',
            ])
            ->mutateFormDataUsing(function (array $data, array $arguments): array {
                if (filled($arguments['status'] ?? null) && array_key_exists($arguments['status'], static::$leadBoardStatuses)) {
                    $data['status'] = $arguments['status'];
                }

                $data['updated_by'] = Auth::id();

                return $data;
            })
            ->successNotificationTitle('Lead created')
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

    protected function getAdditionalViewTypeQueryParameters(): array
    {
        return [
            'pipeline_id' => $this->pipelineId,
        ];
    }

    public function getLeadPipelines(): array
    {
        return [
            [
                'id' => 'default',
                'name' => 'Default Pipeline',
            ],
        ];
    }

    public function getActiveLeadPipeline(): array
    {
        return collect($this->getLeadPipelines())
            ->firstWhere('id', $this->pipelineId)
            ?? $this->getLeadPipelines()[0];
    }

    public function getLeadPipelineTotalValue(): float
    {
        return (float) Lead::query()->sum('expected_value');
    }

    public function getLeadKanbanColumns(): array
    {
        $leads = Lead::query()
            ->with(['assignedUser', 'contact.organization', 'organization'])
            ->orderByDesc('updated_at')
            ->get()
            ->groupBy('status');

        return collect(static::$leadBoardStatuses)
            ->map(function (array $meta, string $status) use ($leads): array {
                /** @var Collection<int, Lead> $columnLeads */
                $columnLeads = $leads->get($status, new Collection());

                return [
                    'key' => $status,
                    'label' => $meta['label'],
                    'description' => $meta['description'],
                    'accent' => $meta['accent'],
                    'lead_count' => $columnLeads->count(),
                    'total_value' => (float) $columnLeads->sum(fn (Lead $lead) => (float) ($lead->expected_value ?? 0)),
                    'leads' => $columnLeads,
                ];
            })
            ->values()
            ->all();
    }

    public function moveLeadToStatus(string $leadId, string $status): void
    {
        $this->applyLeadStatusTransition($leadId, $status);
    }

    public function requestLeadStatusChange(string $leadId, string $status): mixed
    {
        if (! array_key_exists($status, static::$leadBoardStatuses)) {
            return null;
        }

        if (in_array($status, ['converted', 'lost'], true)) {
            $lead = Lead::query()->find($leadId);

            if (! $lead) {
                return null;
            }

            $this->pendingLeadId = $leadId;
            $this->pendingLeadStatus = $status;
            $this->pendingLeadClosedAt = optional($lead->closed_at ?? $lead->converted_at ?? now())->format('Y-m-d\TH:i');
            $this->pendingLeadValue = filled($lead->expected_value)
                ? number_format((float) $lead->expected_value, 2, '.', '')
                : null;
            $this->pendingLeadLostReason = $lead->lost_reason ?: data_get($lead->notes, 'lost_reason');

            return null;
        }

        $this->applyLeadStatusTransition($leadId, $status);

        return null;
    }

    public function cancelPendingLeadStage(): void
    {
        $this->pendingLeadId = null;
        $this->pendingLeadStatus = null;
        $this->pendingLeadClosedAt = null;
        $this->pendingLeadLostReason = null;
        $this->pendingLeadValue = null;
    }

    public function confirmPendingLeadStage(): void
    {
        if (! $this->pendingLeadId || ! $this->pendingLeadStatus) {
            return;
        }

        $status = $this->pendingLeadStatus;

        $rules = [
            'pendingLeadClosedAt' => ['required', 'date'],
        ];

        if ($status === 'converted') {
            $rules['pendingLeadValue'] = ['required', 'numeric'];
        }

        if ($status === 'lost') {
            $rules['pendingLeadLostReason'] = ['required', 'string'];
        }

        $this->validate($rules);

        $this->applyLeadStatusTransition($this->pendingLeadId, $status, [
            'closed_at' => $this->pendingLeadClosedAt,
            'lead_value' => $this->pendingLeadValue,
            'lost_reason' => $this->pendingLeadLostReason,
        ]);

        $this->cancelPendingLeadStage();
    }

    protected function applyLeadStatusTransition(string $leadId, string $status, array $data = []): void
    {
        if (! array_key_exists($status, static::$leadBoardStatuses)) {
            return;
        }

        $lead = Lead::query()->find($leadId);

        if (! $lead) {
            return;
        }

        if ($lead->status === $status) {
            return;
        }

        $lead->status = $status;
        $lead->updated_by = Auth::id();

        $closedAt = filled($data['closed_at'] ?? null) ? $data['closed_at'] : now();

        $lead->closed_at = in_array($status, ['converted', 'lost'], true) ? $closedAt : null;
        $lead->lost_reason = $status === 'lost' ? ($data['lost_reason'] ?? null) : null;

        if ($status === 'converted') {
            $lead->converted_at = $closedAt;
        } else {
            $lead->converted_at = null;
        }

        if (in_array($status, ['contacted', 'quoted', 'booked', 'converted'], true)) {
            $lead->last_contacted_at = $closedAt;
        }

        if ($status === 'converted' && filled($data['lead_value'] ?? null)) {
            $lead->expected_value = $data['lead_value'];
        }

        if ($status === 'lost') {
            $lead->last_contacted_at = $closedAt;
        }

        if ($status !== 'lost' && $status !== 'converted') {
            $lead->closed_at = null;
        }

        $lead->save();

        Notification::make()
            ->title('Lead stage updated')
            ->body("{$lead->name} moved to " . static::$leadBoardStatuses[$status]['label'] . '.')
            ->success()
            ->send();

        $this->resetTable();
    }

    public function getPreviousLeadStatus(string $status): ?string
    {
        $keys = array_keys(static::$leadBoardStatuses);
        $index = array_search($status, $keys, true);

        if (($index === false) || ($index === 0)) {
            return null;
        }

        return $keys[$index - 1];
    }

    public function getNextLeadStatus(string $status): ?string
    {
        $keys = array_keys(static::$leadBoardStatuses);
        $index = array_search($status, $keys, true);

        if (($index === false) || ($index === (count($keys) - 1))) {
            return null;
        }

        return $keys[$index + 1];
    }

    public function getLeadStatusLabel(string $status): string
    {
        return static::$leadBoardStatuses[$status]['label'] ?? $status;
    }

    public function getLeadInitials(Lead $lead): string
    {
        return (string) str($lead->name)
            ->trim()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $segment): string => str($segment)->substr(0, 1)->upper()->toString())
            ->implode('');
    }

    public function getLeadOrganizationName(Lead $lead): ?string
    {
        return $lead->organization?->name
            ?? $lead->contact?->organization?->name;
    }

    public function getLeadCardPills(Lead $lead): array
    {
        $pills = [];

        if (filled($lead->assignedUser?->full_name)) {
            $pills[] = [
                'label' => $lead->assignedUser->full_name,
                'icon' => 'heroicon-m-user',
            ];
        }

        $pills[] = [
            'label' => filled($lead->expected_value)
                ? '$' . number_format((float) $lead->expected_value, 2)
                : '$0.00',
            'icon' => null,
        ];

        if (filled($lead->source)) {
            $pills[] = [
                'label' => str($lead->source)->replace('_', ' ')->headline()->toString(),
                'icon' => null,
            ];
        }

        return $pills;
    }

    public function getLeadAvatarStyle(Lead $lead): string
    {
        $palettes = [
            ['background' => '#d1fae5', 'foreground' => '#065f46'],
            ['background' => '#ffe4e6', 'foreground' => '#9f1239'],
            ['background' => '#fef3c7', 'foreground' => '#92400e'],
            ['background' => '#dbeafe', 'foreground' => '#1d4ed8'],
            ['background' => '#ede9fe', 'foreground' => '#6d28d9'],
            ['background' => '#cffafe', 'foreground' => '#0f766e'],
        ];

        $palette = $palettes[crc32((string) $lead->getKey()) % count($palettes)];

        return sprintf('background-color: %s; color: %s;', $palette['background'], $palette['foreground']);
    }

    public function getLeadColumnClasses(string $accent): string
    {
        return match ($accent) {
            'blue' => 'border-slate-200/90 bg-white dark:border-white/10 dark:bg-slate-900',
            'amber' => 'border-slate-200/90 bg-white dark:border-white/10 dark:bg-slate-900',
            'emerald' => 'border-slate-200/90 bg-white dark:border-white/10 dark:bg-slate-900',
            'teal' => 'border-slate-200/90 bg-white dark:border-white/10 dark:bg-slate-900',
            'rose' => 'border-slate-200/90 bg-white dark:border-white/10 dark:bg-slate-900',
            default => 'border-slate-200/90 bg-white dark:border-white/10 dark:bg-slate-900',
        };
    }

    public function getLeadColumnBadgeClasses(string $accent): string
    {
        return match ($accent) {
            'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-200',
            'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200',
            'emerald' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200',
            'teal' => 'bg-teal-100 text-teal-700 dark:bg-teal-500/15 dark:text-teal-200',
            'rose' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-200',
            default => 'bg-slate-100 text-slate-700 dark:bg-slate-700/80 dark:text-slate-200',
        };
    }
}
