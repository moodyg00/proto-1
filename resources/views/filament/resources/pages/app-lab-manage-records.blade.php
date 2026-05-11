<x-filament-panels::page
    @class([
        'fi-resource-list-records-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    <div class="flex flex-col gap-y-6">
        <x-filament-panels::resources.tabs />

        @if ($this->hasViewSwitcher())
            <div class="flex items-center justify-end">
                <div class="app-surface-panel flex items-center gap-1 rounded-xl border p-1 shadow-sm">
                    @foreach ($this->getAvailableViewTypes() as $viewType => $meta)
                        @if ($this->viewType === $viewType)
                            <span class="app-surface-raised inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium shadow-sm" aria-current="page">
                                <x-filament::icon :icon="$meta['icon']" class="h-5 w-5" />
                                <span>{{ $meta['label'] }}</span>
                            </span>
                        @else
                            <a href="{{ $this->getViewTypeUrl($viewType) }}" class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Switch to {{ strtolower($meta['label']) }} view">
                                <x-filament::icon :icon="$meta['icon']" class="h-5 w-5" />
                                <span>{{ $meta['label'] }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE, scopes: $this->getRenderHookScopes()) }}

        @if ($this->isKanbanView())
            @include('filament.resources.pages.partials.record-kanban')
        @elseif ($this->isCardView())
            @include('filament.resources.pages.partials.record-cards')
        @else
            {{ $this->table }}
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_AFTER, scopes: $this->getRenderHookScopes()) }}
    </div>
</x-filament-panels::page>