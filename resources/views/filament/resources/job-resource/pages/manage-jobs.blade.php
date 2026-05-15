<x-filament-panels::page
    @class([
        'fi-resource-list-records-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    @php
        $listUrl = $this->getViewTypeUrl('list');
        $cardUrl = $this->getViewTypeUrl('card');
        $kanbanUrl = $this->getViewTypeUrl('kanban');
    @endphp

    <div class="flex flex-col gap-y-6">
        <x-filament-panels::resources.tabs />

        <section class="app-surface-panel lead-board-banner sticky top-[4.75rem] z-[1] rounded-xl border px-4 py-3 shadow-sm backdrop-blur">
            <div class="flex items-center justify-between gap-4 max-lg:flex-wrap">
                <div class="flex flex-col gap-1">
                    <div class="text-sm text-slate-500 dark:text-slate-400">Operations</div>
                    <div class="text-2xl font-semibold tracking-tight text-slate-950 dark:text-slate-50">Work Orders</div>
                </div>

                <div class="flex items-center gap-3 max-sm:w-full max-sm:flex-wrap max-sm:justify-end">
                    {{ $this->createWorkOrderAction }}
                </div>
            </div>
        </section>

        <div class="hidden">
            {{ $this->editWorkOrderAction }}
            {{ $this->editBookingAction }}
        </div>

        <div class="flex items-center justify-end gap-3 max-md:w-full max-md:justify-between">
            <div class="app-surface-panel lead-board-control flex items-center gap-1 rounded-xl border p-1 shadow-sm">
                @if ($this->isKanbanView())
                    <span class="app-surface-raised lead-board-switch-active rounded-lg p-2 shadow-sm" aria-label="Kanban view active">
                        <x-filament::icon icon="heroicon-m-view-columns" class="h-5 w-5" />
                    </span>

                    <a href="{{ $cardUrl }}" class="lead-board-switch-action rounded-lg p-2 transition" aria-label="Card view">
                        <x-filament::icon icon="heroicon-m-squares-2x2" class="h-5 w-5" />
                    </a>

                    <a href="{{ $listUrl }}" class="lead-board-switch-action rounded-lg p-2 transition" aria-label="List view">
                        <x-filament::icon icon="heroicon-m-list-bullet" class="h-5 w-5" />
                    </a>
                @elseif ($this->isCardView())
                    <a href="{{ $kanbanUrl }}" class="lead-board-switch-action rounded-lg p-2 transition" aria-label="Kanban view">
                        <x-filament::icon icon="heroicon-m-view-columns" class="h-5 w-5" />
                    </a>

                    <span class="app-surface-raised lead-board-switch-active rounded-lg p-2 shadow-sm" aria-label="Card view active">
                        <x-filament::icon icon="heroicon-m-squares-2x2" class="h-5 w-5" />
                    </span>

                    <a href="{{ $listUrl }}" class="lead-board-switch-action rounded-lg p-2 transition" aria-label="List view">
                        <x-filament::icon icon="heroicon-m-list-bullet" class="h-5 w-5" />
                    </a>
                @else
                    <a href="{{ $kanbanUrl }}" class="lead-board-switch-action rounded-lg p-2 transition" aria-label="Kanban view">
                        <x-filament::icon icon="heroicon-m-view-columns" class="h-5 w-5" />
                    </a>

                    <a href="{{ $cardUrl }}" class="lead-board-switch-action rounded-lg p-2 transition" aria-label="Card view">
                        <x-filament::icon icon="heroicon-m-squares-2x2" class="h-5 w-5" />
                    </a>

                        <span class="app-surface-raised lead-board-switch-active rounded-lg p-2 shadow-sm" aria-label="List view active">
                        <x-filament::icon icon="heroicon-m-list-bullet" class="h-5 w-5" />
                    </span>
                @endif
            </div>
        </div>

        @if (! $this->isKanbanView())
            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE, scopes: $this->getRenderHookScopes()) }}

            @if ($this->isCardView())
                @include('filament.resources.pages.partials.record-view-toolbar')

                <div hidden aria-hidden="true">
                    {{ $this->table }}
                </div>

                <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
                    @foreach ($this->getCardViewItems() as $job)
                        @php
                            $cardActions = $this->getCardViewActions($job);
                        @endphp

                        <article
                            wire:key="job-card-view-{{ $job->getKey() }}"
                            class="app-surface-raised lead-board-card flex flex-col gap-5 rounded-xl border p-4 transition"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold leading-5 text-slate-900 dark:text-slate-100">{{ $job->work_order_number }}</div>
                                    <div class="mt-1 text-xs leading-4 text-slate-500 dark:text-slate-400">{{ $job->customer_name ?: 'Unknown customer' }}</div>
                                </div>

                                <div class="app-surface-elevated lead-board-pill inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium">
                                    {{ \App\Models\Job::statusOptions()[\App\Models\Job::normalizeStatus($job->status)] ?? str($job->status)->headline()->toString() }}
                                </div>
                            </div>

                            <p class="text-sm font-medium leading-6 text-slate-800 dark:text-slate-100">{{ $job->service_name ?: 'Unscheduled service' }}</p>

                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($this->getJobCardPills($job) as $pill)
                                    <div class="app-surface-elevated lead-board-pill inline-flex items-center gap-1 rounded-full border px-2.5 py-1.5 text-xs font-medium">
                                        @if ($pill['icon'])
                                            <x-filament::icon :icon="$pill['icon']" class="h-3.5 w-3.5" />
                                        @endif

                                        <span>{{ $pill['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-auto flex flex-wrap items-center justify-between gap-2 pt-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    @foreach ($cardActions as $action)
                                        @php
                                            $buttonClasses = match ($action['style'] ?? 'secondary') {
                                                'primary' => 'bg-primary-600 text-white hover:bg-primary-500',
                                                'danger' => 'bg-rose-600 text-white hover:bg-rose-500',
                                                default => 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700',
                                            };
                                        @endphp

                                        @if ($action['type'] === 'url')
                                            <a
                                                href="{{ $action['url'] }}"
                                                @if ($action['new_tab']) target="_blank" rel="noopener noreferrer" @endif
                                                class="inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold transition {{ $buttonClasses }}"
                                            >
                                                {{ $action['label'] }}
                                            </a>
                                        @else
                                            <button
                                                type="button"
                                                wire:click.stop="mountTableAction('{{ $action['name'] }}', '{{ $action['record'] }}')"
                                                class="inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold transition {{ $buttonClasses }}"
                                            >
                                                {{ $action['label'] }}
                                            </button>
                                        @endif
                                    @endforeach
                                </div>

                                {{ ($this->editBookingAction)(['job' => $job->getKey()])
                                    ->label($job->booking()->exists() ? 'Edit Booking' : 'Create Booking')
                                    ->size(
                                        \Filament\Support\Enums\ActionSize::Small,
                                    )
                                    ->color('gray') }}
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                {{ $this->table }}
            @endif

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER, scopes: $this->getRenderHookScopes()) }}
        @else
            <div
                x-data="{
                    sortables: [],
                    draggedJobId: null,
                    activeDropStatus: null,
                    suppressNextClick: false,
                    initSortables() {
                        if (typeof window.Sortable === 'undefined') {
                            return
                        }

                        this.destroySortables()

                        this.$el.querySelectorAll('[data-job-list]').forEach((list) => {
                            const sortable = window.Sortable.create(list, {
                                group: 'work-orders',
                                sort: true,
                                draggable: '[data-draggable]',
                                handle: '.lead-item',
                                ignore: '',
                                animation: 150,
                                forceFallback: false,
                                fallbackTolerance: 0,
                                delay: 0,
                                delayOnTouchOnly: false,
                                touchStartThreshold: 2,
                                ghostClass: 'lead-board-sortable-ghost',
                                chosenClass: 'lead-board-sortable-chosen',
                                dragClass: 'lead-board-sortable-drag',
                                onStart: (event) => {
                                    this.draggedJobId = event.item?.dataset.jobCard ?? null
                                },
                                onMove: (event) => {
                                    this.activeDropStatus = event.to.closest('[data-job-status]')?.dataset.jobStatus ?? null
                                },
                                onEnd: (event) => {
                                    const jobId = event.item?.dataset.jobCard
                                    const fromStatus = event.from.closest('[data-job-status]')?.dataset.jobStatus
                                    const toStatus = event.to.closest('[data-job-status]')?.dataset.jobStatus

                                    if (jobId && toStatus && fromStatus !== toStatus) {
                                        $wire.requestJobStatusChange(jobId, toStatus)
                                    }

                                    this.draggedJobId = null
                                    this.activeDropStatus = null
                                },
                            })

                            this.sortables.push(sortable)
                        })
                    },
                    destroySortables() {
                        this.sortables.forEach((sortable) => sortable.destroy())
                        this.sortables = []
                    },
                }"
                x-init="$nextTick(() => initSortables())"
                class="flex gap-3 overflow-x-auto pb-2"
            >
                @foreach ($this->getWorkOrderKanbanColumns() as $column)
                    <div
                        data-job-status="{{ $column['key'] }}"
                        x-bind:class="activeDropStatus === '{{ $column['key'] }}' ? 'ring-2 ring-primary-400 ring-offset-2' : ''"
                        class="app-surface-panel lead-board-column flex min-w-[18rem] max-w-[18rem] flex-col gap-1 rounded-xl border p-0 shadow-sm transition {{ $this->getJobColumnClasses($column['accent']) }}"
                    >
                        <div class="flex flex-col gap-2 px-4 py-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $column['label'] }} ({{ $column['job_count'] }})</span>
                                {{ ($this->quickCreateWorkOrderAction)(['status' => $column['key']]) }}
                            </div>
                        </div>

                        <div data-job-list class="flex max-h-[calc(100vh-21rem)] min-h-[26rem] flex-col gap-3 overflow-y-auto p-3">
                            @forelse ($column['jobs'] as $job)
                                @php
                                    $cardActions = $this->getCardViewActions($job);
                                @endphp

                                <article
                                    wire:key="job-board-card-{{ $job->getKey() }}"
                                    data-draggable
                                    data-job-card="{{ $job->getKey() }}"
                                    class="app-surface-raised lead-board-card lead-item flex cursor-move flex-col gap-5 rounded-xl border p-4 transition"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold leading-5 text-slate-900 dark:text-slate-100">{{ $job->work_order_number }}</div>
                                            <div class="mt-1 text-xs leading-4 text-slate-500 dark:text-slate-400">{{ $job->customer_name ?: 'Unknown customer' }}</div>
                                        </div>

                                        <div class="app-surface-elevated lead-board-pill inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium">
                                            {{ \App\Models\Job::statusOptions()[\App\Models\Job::normalizeStatus($job->status)] ?? str($job->status)->headline()->toString() }}
                                        </div>
                                    </div>

                                    <p class="text-sm font-medium leading-6 text-slate-800 dark:text-slate-100">{{ $job->service_name ?: 'Unscheduled service' }}</p>

                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($this->getJobCardPills($job) as $pill)
                                            <div class="app-surface-elevated lead-board-pill inline-flex items-center gap-1 rounded-full border px-2.5 py-1.5 text-xs font-medium">
                                                @if ($pill['icon'])
                                                    <x-filament::icon :icon="$pill['icon']" class="h-3.5 w-3.5" />
                                                @endif

                                                <span>{{ $pill['label'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="mt-auto flex flex-wrap items-center justify-between gap-2 pt-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            @foreach ($cardActions as $action)
                                                @php
                                                    $buttonClasses = match ($action['style'] ?? 'secondary') {
                                                        'primary' => 'bg-primary-600 text-white hover:bg-primary-500',
                                                        'danger' => 'bg-rose-600 text-white hover:bg-rose-500',
                                                        default => 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700',
                                                    };
                                                @endphp

                                                @if ($action['type'] === 'url')
                                                    <a
                                                        href="{{ $action['url'] }}"
                                                        @if ($action['new_tab']) target="_blank" rel="noopener noreferrer" @endif
                                                        x-on:click.stop
                                                        class="inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold transition {{ $buttonClasses }}"
                                                    >
                                                        {{ $action['label'] }}
                                                    </a>
                                                @else
                                                    <button
                                                        type="button"
                                                        wire:click.stop="mountTableAction('{{ $action['name'] }}', '{{ $action['record'] }}')"
                                                        x-on:click.stop
                                                        class="inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold transition {{ $buttonClasses }}"
                                                    >
                                                        {{ $action['label'] }}
                                                    </button>
                                                @endif
                                            @endforeach
                                        </div>

                                        {{ ($this->editBookingAction)(['job' => $job->getKey()])
                                            ->label($job->booking()->exists() ? 'Edit Booking' : 'Create Booking')
                                            ->size(
                                                \Filament\Support\Enums\ActionSize::Small,
                                            )
                                            ->color('gray') }}
                                    </div>
                                </article>
                            @empty
                                <div class="app-surface-raised lead-board-card flex h-full min-h-[18rem] flex-col items-center justify-center gap-3 rounded-xl border border-dashed p-5 text-center">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-700 dark:text-slate-300">
                                        <x-filament::icon icon="heroicon-m-briefcase" class="h-6 w-6" />
                                    </div>

                                    <div class="space-y-1">
                                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">No work orders here</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $column['description'] }}</p>
                                    </div>

                                    {{ ($this->quickCreateWorkOrderAction)(['status' => $column['key']]) }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <x-filament-actions::modals />
    </div>
</x-filament-panels::page>