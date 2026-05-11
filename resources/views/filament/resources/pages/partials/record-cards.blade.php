@php
    $cardItems = $this->getCardViewItems();
@endphp

@include('filament.resources.pages.partials.record-view-toolbar')

<div hidden aria-hidden="true">
    {{ $this->table }}
</div>

@if ($cardItems->isEmpty())
    <div class="app-surface-panel rounded-2xl border border-dashed px-6 py-12 text-center shadow-sm">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-300">
            <x-filament::icon icon="heroicon-m-squares-2x2" class="h-6 w-6" />
        </div>

        <div class="mt-4 space-y-1">
            <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">No records found</p>
            <p class="text-sm text-slate-500 dark:text-slate-400">Adjust your filters or add a new record to populate this view.</p>
        </div>
    </div>
@else
    <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
        @foreach ($cardItems as $record)
            @php
                $primaryAction = $this->getCardViewPrimaryAction($record);
            @endphp

            <article class="app-surface-panel flex h-full flex-col gap-4 rounded-2xl border p-5 shadow-sm">
                <div class="space-y-1">
                    <h3 class="text-base font-semibold leading-6 text-slate-950 dark:text-slate-50">{{ $this->getCardViewTitle($record) }}</h3>
                </div>

                <dl class="grid gap-3 sm:grid-cols-2">
                    @foreach ($this->getCardViewFields($record) as $field)
                        <div class="space-y-1">
                            <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">{{ $field['label'] }}</dt>
                            <dd class="text-sm text-slate-800 dark:text-slate-100">{{ $field['value'] }}</dd>
                        </div>
                    @endforeach
                </dl>

                @if ($primaryAction)
                    <div class="mt-auto flex items-center justify-end">
                        @if ($primaryAction['type'] === 'url')
                            <a
                                href="{{ $primaryAction['url'] }}"
                                @if ($primaryAction['new_tab']) target="_blank" rel="noopener noreferrer" @endif
                                class="inline-flex items-center rounded-xl bg-primary-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-primary-500"
                            >
                                {{ $primaryAction['label'] }}
                            </a>
                        @else
                            <button
                                type="button"
                                wire:click="mountTableAction('{{ $primaryAction['name'] }}', '{{ $primaryAction['record'] }}')"
                                class="inline-flex items-center rounded-xl bg-primary-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-primary-500"
                            >
                                {{ $primaryAction['label'] }}
                            </button>
                        @endif
                    </div>
                @endif
            </article>
        @endforeach
    </div>

@endif