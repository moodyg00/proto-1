@php
    $table = $this->getTable();
    $hasSearch = $table->isSearchable();
    $hasFilters = $table->isFilterable();
    $hasToggleColumns = $table->hasToggleableColumns();
    $filterIndicators = $table->getFilterIndicators();
@endphp

@if ($hasSearch || $hasFilters || $hasToggleColumns || count($filterIndicators))
    <div class="space-y-3">
        <div class="app-surface-panel flex items-center justify-between gap-4 rounded-2xl border px-4 py-3 shadow-sm">
            <div class="flex items-center gap-4">
                @if ($hasSearch)
                    <x-filament-tables::search-field
                        :debounce="$table->getSearchDebounce()"
                        :on-blur="$table->isSearchOnBlur()"
                        :placeholder="$table->getSearchPlaceholder()"
                    />
                @endif
            </div>

            @if ($hasFilters || $hasToggleColumns)
                <div class="ms-auto flex items-center gap-3">
                    @if ($hasFilters)
                        <x-filament-tables::filters.dialog
                            :active-filters-count="$table->getActiveFiltersCount()"
                            :apply-action="$table->getFiltersApplyAction()"
                            :form="$table->getFiltersForm()"
                            :layout="$table->getFiltersLayout()"
                            :max-height="$table->getFiltersFormMaxHeight()"
                            :trigger-action="$table->getFiltersTriggerAction()"
                            :width="$table->getFiltersFormWidth()"
                        />
                    @endif

                    @if ($hasToggleColumns)
                        <x-filament-tables::column-toggle.dropdown
                            :form="$table->getColumnToggleForm()"
                            :max-height="$table->getColumnToggleFormMaxHeight()"
                            :trigger-action="$table->getToggleColumnsTriggerAction()"
                            :width="$table->getColumnToggleFormWidth()"
                        />
                    @endif
                </div>
            @endif
        </div>

        @if (count($filterIndicators))
            <x-filament-tables::filters.indicators :indicators="$filterIndicators" />
        @endif
    </div>
@endif