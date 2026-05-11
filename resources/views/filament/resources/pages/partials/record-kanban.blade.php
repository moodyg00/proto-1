<div class="space-y-6">
    @include('filament.resources.pages.partials.record-view-toolbar')

    <div hidden aria-hidden="true">
        {{ $this->table }}
    </div>

    <div
        x-data="{
            sortables: [],
            initSortables() {
                if (typeof window.Sortable === 'undefined') {
                    return
                }

                this.destroySortables()

                this.$el.querySelectorAll('[data-kanban-list]').forEach((list) => {
                    const sortable = window.Sortable.create(list, {
                        group: 'shared-kanban',
                        sort: true,
                        draggable: '[data-draggable]',
                        handle: '.kanban-card',
                        animation: 150,
                        onEnd: (event) => {
                            const recordKey = event.item?.dataset.kanbanRecord
                            const fromStatus = event.from.closest('[data-kanban-status]')?.dataset.kanbanStatus
                            const toStatus = event.to.closest('[data-kanban-status]')?.dataset.kanbanStatus

                            if (recordKey && toStatus && fromStatus !== toStatus) {
                                $wire.requestKanbanStatusChange(recordKey, toStatus)
                            }
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
        @foreach ($this->getKanbanColumns() as $column)
            <section
                data-kanban-status="{{ $column['key'] }}"
                class="app-surface-panel flex min-w-[18rem] max-w-[18rem] flex-col gap-1 rounded-xl border p-0 shadow-sm transition {{ $this->getKanbanColumnClasses($column['accent']) }}"
            >
                <div class="flex flex-col gap-1 px-4 py-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $column['label'] }} ({{ $column['count'] }})</span>
                    </div>

                    @if (filled($column['description']))
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $column['description'] }}</p>
                    @endif
                </div>

                <div data-kanban-list class="flex min-h-[24rem] flex-col gap-3 p-3">
                    @forelse ($column['records'] as $record)
                        @php
                            $action = $this->getKanbanCardAction($record);
                        @endphp

                        <article data-draggable data-kanban-record="{{ $this->getTableRecordKey($record) }}" class="app-surface-raised kanban-card flex cursor-move flex-col gap-4 rounded-xl border p-4 transition">
                            <div class="space-y-1">
                                <h3 class="text-sm font-semibold leading-5 text-slate-900 dark:text-slate-100">{{ $this->getCardViewTitle($record) }}</h3>
                            </div>

                            <dl class="grid gap-3">
                                @foreach ($this->getCardViewFields($record) as $field)
                                    <div class="space-y-1">
                                        <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">{{ $field['label'] }}</dt>
                                        <dd class="text-sm text-slate-800 dark:text-slate-100">{{ $field['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>

                            @if ($action)
                                <div class="mt-auto flex items-center justify-end">
                                    @if ($action['type'] === 'url')
                                        <a
                                            href="{{ $action['url'] }}"
                                            @if ($action['new_tab']) target="_blank" rel="noopener noreferrer" @endif
                                            class="inline-flex items-center rounded-xl bg-primary-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-primary-500"
                                        >
                                            {{ $action['label'] }}
                                        </a>
                                    @else
                                        <button
                                            type="button"
                                            wire:click="mountTableAction('{{ $action['name'] }}', '{{ $action['record'] }}')"
                                            class="inline-flex items-center rounded-xl bg-primary-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-primary-500"
                                        >
                                            {{ $action['label'] }}
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="app-surface-raised flex h-full min-h-[14rem] flex-col items-center justify-center gap-3 rounded-xl border border-dashed p-5 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-700 dark:text-slate-300">
                                <x-filament::icon icon="heroicon-m-view-columns" class="h-5 w-5" />
                            </div>

                            <div class="space-y-1">
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">No records here</p>
                                @if (filled($column['description']))
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $column['description'] }}</p>
                                @endif
                            </div>
                        </div>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</div>