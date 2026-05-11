<x-filament-panels::page
    @class([
        'fi-resource-list-records-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    @php
        $activePipeline = $this->getActiveLeadPipeline();
        $listUrl = $this->getViewTypeUrl('list');
        $cardUrl = $this->getViewTypeUrl('card');
        $kanbanUrl = $this->getViewTypeUrl('kanban');
        $pipelineTotalValue = max($this->getLeadPipelineTotalValue(), 1);
    @endphp

    <div class="flex flex-col gap-y-6">
        <x-filament-panels::resources.tabs />

        @if ($pendingLeadId && $pendingLeadStatus)
            <template x-teleport="body">
                <div
                    class="fixed inset-0 z-[220] flex items-center justify-center bg-slate-950/88 px-4 py-6 backdrop-blur-md"
                    x-on:click.self="$wire.cancelPendingLeadStage()"
                    x-on:keydown.escape.window="$wire.cancelPendingLeadStage()"
                >
                    <div
                        class="app-surface-panel w-full max-w-xl rounded-2xl border p-6 shadow-2xl"
                    >
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-1">
                            <h3 class="text-lg font-semibold text-slate-950 dark:text-slate-50">
                                {{ $pendingLeadStatus === 'converted' ? 'Move lead to Won' : 'Move lead to Lost' }}
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                {{ $pendingLeadStatus === 'converted'
                                    ? 'Krayin asks for final deal details before a lead is marked as won. Capture that here before the card moves.'
                                    : 'Krayin asks for a close-out reason before a lead is marked as lost. Capture that here before the card moves.' }}
                            </p>
                        </div>

                        <button
                            type="button"
                            wire:click="cancelPendingLeadStage"
                            class="rounded-lg p-2 text-slate-400 transition hover:!bg-slate-100 hover:text-slate-700 dark:hover:!bg-slate-800 dark:hover:!text-slate-100"
                            aria-label="Close lead stage modal"
                        >
                            <x-filament::icon icon="heroicon-m-x-mark" class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="mt-6 space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-800 dark:text-slate-200">Closed at</label>
                            <input
                                type="datetime-local"
                                wire:model="pendingLeadClosedAt"
                                class="app-surface-inset w-full rounded-xl border px-3 py-2.5 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                            >
                            @error('pendingLeadClosedAt')
                                <p class="text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($pendingLeadStatus === 'converted')
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-slate-800 dark:text-slate-200">Won value</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    wire:model="pendingLeadValue"
                                    class="app-surface-inset w-full rounded-xl border px-3 py-2.5 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                                >
                                @error('pendingLeadValue')
                                    <p class="text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        @if ($pendingLeadStatus === 'lost')
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-slate-800 dark:text-slate-200">Lost reason</label>
                                <textarea
                                    rows="4"
                                    wire:model="pendingLeadLostReason"
                                    class="app-surface-inset w-full rounded-xl border px-3 py-2.5 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                                ></textarea>
                                @error('pendingLeadLostReason')
                                    <p class="text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            wire:click="cancelPendingLeadStage"
                            class="app-surface-raised rounded-xl border px-4 py-2 text-sm font-medium transition"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            wire:click="confirmPendingLeadStage"
                            class="rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-500"
                        >
                            Save and move
                        </button>
                    </div>
                </div>
                </div>
            </template>
        @endif

        <section class="app-surface-panel lead-board-banner sticky top-[4.75rem] z-[1] rounded-xl border px-4 py-3 shadow-sm backdrop-blur">
            <div class="flex items-center justify-between gap-4 max-lg:flex-wrap">
                <div class="flex flex-col gap-1">
                    <div class="text-sm text-slate-500 dark:text-slate-400">Customer Relations</div>
                    <div class="text-2xl font-semibold tracking-tight text-slate-950 dark:text-slate-50">Leads</div>
                </div>

                <div class="flex items-center gap-3 max-sm:w-full max-sm:flex-wrap max-sm:justify-end">
                    {{ $this->createLeadAction }}
                </div>
            </div>
        </section>

        <div class="hidden">
            {{ $this->editLeadAction }}
        </div>

        <div class="flex items-center justify-between gap-3 max-lg:flex-wrap">
            <div class="flex w-full items-center gap-3 max-lg:justify-between">
                <div class="hidden h-10 w-full rounded-xl border border-dashed border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900/70 lg:block"></div>
            </div>

            <div class="flex items-center gap-3 max-md:w-full max-md:justify-between">
                <x-filament::dropdown placement="bottom-start" width="xs">
                    <x-slot name="trigger">
                        <button
                            type="button"
                            class="app-surface-panel lead-board-control inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-sm font-medium shadow-sm transition"
                        >
                            <span>{{ $activePipeline['name'] }}</span>
                            <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" />
                        </button>
                    </x-slot>

                    <x-filament::dropdown.list>
                        @foreach ($this->getLeadPipelines() as $pipeline)
                            <x-filament::dropdown.list.item
                                :href="$this->getViewTypeUrl($this->viewType ?? 'kanban', ['pipeline_id' => $pipeline['id']])"
                            >
                                {{ $pipeline['name'] }}
                            </x-filament::dropdown.list.item>
                        @endforeach
                    </x-filament::dropdown.list>
                </x-filament::dropdown>

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
        </div>

        @if (! $this->isKanbanView())
            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE, scopes: $this->getRenderHookScopes()) }}

            @if ($this->isCardView())
                @include('filament.resources.pages.partials.record-view-toolbar')

                <div hidden aria-hidden="true">
                    {{ $this->table }}
                </div>

                <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
                    @foreach ($this->getCardViewItems() as $lead)
                        <a
                            wire:key="lead-card-view-{{ $lead->getKey() }}"
                            href="#"
                            x-on:click.prevent="$wire.mountAction('editLead', { lead: '{{ $lead->getKey() }}' })"
                            class="app-surface-raised lead-board-card flex flex-col gap-5 rounded-xl border p-4 transition"
                        >
                            <div class="flex items-start gap-3">
                                <div class="flex items-start gap-3 min-w-0">
                                    <div class="lead-board-avatar flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-base font-semibold uppercase tracking-wide" style="{{ $this->getLeadAvatarStyle($lead) }}">
                                        {{ $this->getLeadInitials($lead) }}
                                    </div>

                                    <div class="flex flex-col gap-0.5 pt-0.5">
                                        <span class="text-sm font-semibold leading-5 text-slate-900 dark:text-slate-100">{{ $lead->name }}</span>

                                        @if ($this->getLeadOrganizationName($lead))
                                            <span class="text-xs leading-4 text-slate-500 dark:text-slate-400">{{ $this->getLeadOrganizationName($lead) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <p class="text-sm font-medium leading-6 text-slate-800 dark:text-slate-100">{{ $lead->title ?: 'Untitled lead' }}</p>

                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($this->getLeadCardPills($lead) as $pill)
                                    <div class="app-surface-elevated lead-board-pill inline-flex items-center gap-1 rounded-full border px-2.5 py-1.5 text-xs font-medium">
                                        @if ($pill['icon'])
                                            <x-filament::icon :icon="$pill['icon']" class="h-3.5 w-3.5" />
                                        @endif

                                        <span>{{ $pill['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </a>
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
                    draggedLeadId: null,
                    activeDropStatus: null,
                    suppressNextClick: false,
                    initSortables() {
                        if (typeof window.Sortable === 'undefined') {
                            return
                        }

                        this.destroySortables()

                        this.$el.querySelectorAll('[data-lead-list]').forEach((list) => {
                            const sortable = window.Sortable.create(list, {
                                group: 'leads',
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
                                    this.suppressNextClick = true
                                    this.dragStart(event.item?.dataset.leadCard ?? null)
                                },
                                onMove: (event) => {
                                    this.activeDropStatus = event.to.closest('[data-lead-status]')?.dataset.leadStatus ?? null
                                },
                                onEnd: (event) => {
                                    const leadId = event.item?.dataset.leadCard
                                    const fromStatus = event.from.closest('[data-lead-status]')?.dataset.leadStatus
                                    const toStatus = event.to.closest('[data-lead-status]')?.dataset.leadStatus

                                    if (leadId && toStatus && fromStatus !== toStatus) {
                                        $wire.requestLeadStatusChange(leadId, toStatus)
                                    }

                                    this.dragEnd()
                                },
                            })

                            this.sortables.push(sortable)
                        })
                    },
                    destroySortables() {
                        this.sortables.forEach((sortable) => sortable.destroy())
                        this.sortables = []
                    },
                    dragStart(leadId) {
                        this.draggedLeadId = leadId
                    },
                    dragEnd() {
                        this.draggedLeadId = null
                        this.activeDropStatus = null
                    },
                    setDropStatus(status) {
                        this.activeDropStatus = status
                    },
                    clearDropStatus(status) {
                        if (this.activeDropStatus === status) {
                            this.activeDropStatus = null
                        }
                    },
                    dropLead(status) {
                        if (! this.draggedLeadId) {
                            return
                        }

                        $wire.requestLeadStatusChange(this.draggedLeadId, status)
                        this.dragEnd()
                    },
                    handleCardActivate(leadId) {
                        if (this.suppressNextClick) {
                            this.suppressNextClick = false
                            return
                        }

                        $wire.mountAction('editLead', { lead: leadId })
                    },
                }"
                x-init="$nextTick(() => initSortables())"
                class="flex gap-3 overflow-x-auto pb-2"
            >
                @foreach ($this->getLeadKanbanColumns() as $column)
                    @php
                        $progress = min(($column['total_value'] / $pipelineTotalValue) * 100, 100);
                    @endphp

                    <div
                        data-lead-status="{{ $column['key'] }}"
                        x-bind:class="activeDropStatus === '{{ $column['key'] }}' ? 'ring-2 ring-primary-400 ring-offset-2' : ''"
                        class="app-surface-panel lead-board-column flex min-w-[18rem] max-w-[18rem] flex-col gap-1 rounded-xl border p-0 shadow-sm transition {{ $this->getLeadColumnClasses($column['accent']) }}"
                    >
                        <div class="flex flex-col gap-2 px-4 py-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $column['label'] }} ({{ $column['lead_count'] }})</span>
                                {{ ($this->quickCreateLeadAction)(['status' => $column['key']]) }}
                            </div>

                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-semibold text-slate-800 dark:text-slate-100">${{ number_format($column['total_value'], 2) }}</span>

                                <div class="h-1.5 w-32 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                    <div class="h-1.5 bg-emerald-500" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        </div>

                        <div data-lead-list class="flex max-h-[calc(100vh-21rem)] min-h-[26rem] flex-col gap-3 overflow-y-auto p-3">
                            @forelse ($column['leads'] as $lead)
                                <a
                                    wire:key="lead-board-card-{{ $lead->getKey() }}"
                                    href="#"
                                    data-draggable
                                    data-lead-card="{{ $lead->getKey() }}"
                                    x-on:click.prevent="handleCardActivate('{{ $lead->getKey() }}')"
                                    class="app-surface-raised lead-board-card lead-item flex cursor-move flex-col gap-5 rounded-xl border p-4 transition"
                                >
                                    <div class="flex items-start gap-3">
                                        <div class="flex items-start gap-3 min-w-0">
                                            <div class="lead-board-avatar flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-base font-semibold uppercase tracking-wide" style="{{ $this->getLeadAvatarStyle($lead) }}">
                                                {{ $this->getLeadInitials($lead) }}
                                            </div>

                                            <div class="flex flex-col gap-0.5 pt-0.5">
                                                <span class="text-sm font-semibold leading-5 text-slate-900 dark:text-slate-100">{{ $lead->name }}</span>

                                                @if ($this->getLeadOrganizationName($lead))
                                                    <span class="text-xs leading-4 text-slate-500 dark:text-slate-400">{{ $this->getLeadOrganizationName($lead) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <p class="text-sm font-medium leading-6 text-slate-800 dark:text-slate-100">{{ $lead->title ?: 'Untitled lead' }}</p>

                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($this->getLeadCardPills($lead) as $pill)
                                            <div class="app-surface-elevated lead-board-pill inline-flex items-center gap-1 rounded-full border px-2.5 py-1.5 text-xs font-medium">
                                                @if ($pill['icon'])
                                                    <x-filament::icon :icon="$pill['icon']" class="h-3.5 w-3.5" />
                                                @endif

                                                <span>{{ $pill['label'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </a>
                            @empty
                                <div class="app-surface-raised lead-board-card flex h-full min-h-[18rem] flex-col items-center justify-center gap-3 rounded-xl border border-dashed p-5 text-center">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-700 dark:text-slate-300">
                                        <x-filament::icon icon="heroicon-m-view-columns" class="h-6 w-6" />
                                    </div>

                                    <div class="space-y-1">
                                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">Your Leads List is Empty</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $column['description'] }}</p>
                                    </div>

                                    {{ ($this->quickCreateLeadAction)(['status' => $column['key']]) }}
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