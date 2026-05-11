<?php

namespace App\Filament\Resources\Pages;

use App\Models\Setting;
use Filament\Resources\Pages\ManageRecords;
use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;

abstract class AppLabManageRecords extends ManageRecords
{
    protected static string $view = 'filament.resources.pages.app-lab-manage-records';

    #[Url(as: 'view_type')]
    public ?string $viewType = null;

    public function mount(): void
    {
        $this->primeSavedTableColumnPreferences();
        $this->viewType = $this->resolveInitialViewType();

        parent::mount();

        $this->saveTablePreferences([
            'view_type' => $this->viewType,
        ]);
    }

    public function updatedToggledTableColumns(): void
    {
        parent::updatedToggledTableColumns();

        if (! Auth::check()) {
            return;
        }

        $this->saveTablePreferences([
            'toggled_columns' => $this->toggledTableColumns,
        ]);
    }

    public function updatedViewType(?string $viewType): void
    {
        $this->viewType = $this->normalizeViewType($viewType) ?? $this->getDefaultViewType();

        $this->saveTablePreferences([
            'view_type' => $this->viewType,
        ]);

        $this->flushCachedTableRecords();
        $this->resetPage();
    }

    protected function primeSavedTableColumnPreferences(): void
    {
        if (! Auth::check()) {
            return;
        }

        $state = $this->getStoredTablePreferences();

        $savedColumns = data_get($state, 'toggled_columns');

        if (! is_array($savedColumns) || ($savedColumns === [])) {
            return;
        }

        $this->toggledTableColumns = $savedColumns;

        session()->put([
            $this->getTableColumnToggleFormStateSessionKey() => $savedColumns,
        ]);
    }

    protected function getTablePreferenceKey(): string
    {
        return 'user:' . Auth::id() . ':manage:' . substr(sha1(static::class), 0, 20);
    }

    public function table(Table $table): Table
    {
        return parent::table($table);
    }

    public function getAvailableViewTypes(): array
    {
        return collect($this->getSupportedViewTypes())
            ->mapWithKeys(fn (string $viewType): array => [$viewType => [
                'label' => $this->getViewTypeLabel($viewType),
                'icon' => $this->getViewTypeIcon($viewType),
            ]])
            ->all();
    }

    public function getViewTypeUrl(string $viewType, array $additionalParameters = []): string
    {
        $viewType = $this->normalizeViewType($viewType) ?? $this->getDefaultViewType();
        $query = array_merge(
            $this->getPersistentViewQueryParameters(),
            $this->getAdditionalViewTypeQueryParameters(),
            $additionalParameters,
        );

        $query['view_type'] = $viewType;

        return static::getResource()::getUrl('index', $query);
    }

    public function hasViewSwitcher(): bool
    {
        return count($this->getSupportedViewTypes()) > 1;
    }

    public function getKanbanColumns(): array
    {
        $statusField = $this->getKanbanStatusField();

        if (! $statusField) {
            return [];
        }

        $records = $this->getFilteredSortedTableQuery()
            ->get()
            ->groupBy(fn (Model $record): string => (string) data_get($record, $statusField));

        return collect($this->getKanbanStatuses())
            ->map(function (array $meta, string $status) use ($records): array {
                $columnRecords = $records->get($status, new EloquentCollection());

                return [
                    'key' => $status,
                    'label' => $meta['label'] ?? Str::headline($status),
                    'description' => $meta['description'] ?? null,
                    'accent' => $meta['accent'] ?? 'slate',
                    'count' => $columnRecords->count(),
                    'records' => $columnRecords,
                ];
            })
            ->values()
            ->all();
    }

    public function requestKanbanStatusChange(string $recordKey, string $status): void
    {
        if (! array_key_exists($status, $this->getKanbanStatuses())) {
            return;
        }

        $record = $this->getTableRecord($recordKey);
        $statusField = $this->getKanbanStatusField();

        if (! $record || ! $statusField) {
            return;
        }

        if ((string) data_get($record, $statusField) === $status) {
            return;
        }

        $record->{$statusField} = $status;
        $this->mutateKanbanRecordStatus($record, $status);
        $record->save();

        $this->flushCachedTableRecords();
        $this->resetTable();
    }

    public function getCardViewRecords(): EloquentCollection | Collection | Paginator | CursorPaginator
    {
        return $this->getTableRecords();
    }

    public function getCardViewItems(): Collection
    {
        $records = $this->getCardViewRecords();

        if (($records instanceof Paginator) || ($records instanceof CursorPaginator)) {
            return collect($records->items());
        }

        if ($records instanceof EloquentCollection) {
            return $records->toBase();
        }

        return collect($records);
    }

    public function getCardViewFields(Model $record): array
    {
        $title = $this->getCardViewTitle($record);

        return collect($this->getTable()->getVisibleColumns())
            ->map(function (Column $column) use ($record): ?array {
                $value = $this->formatCardViewField($column, $record);

                if (blank($value)) {
                    return null;
                }

                return [
                    'label' => trim(strip_tags((string) $column->getLabel())),
                    'value' => $value,
                ];
            })
            ->filter()
            ->reject(fn (array $field): bool => $field['value'] === $title)
            ->take($this->getCardViewFieldLimit())
            ->values()
            ->all();
    }

    public function getCardViewPrimaryAction(Model $record): ?array
    {
        $table = $this->getTable();

        if ($url = $table->getRecordUrl($record)) {
            return [
                'type' => 'url',
                'label' => 'Edit',
                'url' => $url,
                'new_tab' => $table->shouldOpenRecordUrlInNewTab($record),
            ];
        }

        foreach (['edit', 'view'] as $actionName) {
            $action = $table->getAction($actionName);

            if (! $action) {
                continue;
            }

            $action->record($record);

            if ($action->isHidden()) {
                continue;
            }

            return [
                'type' => 'table-action',
                'label' => Str::headline($actionName),
                'name' => $actionName,
                'record' => $this->getTableRecordKey($record),
            ];
        }

        return null;
    }

    public function getKanbanCardAction(Model $record): ?array
    {
        return $this->getCardViewPrimaryAction($record);
    }

    public function getCardViewTitle(Model $record): string
    {
        $title = trim(strip_tags((string) static::getResource()::getRecordTitle($record)));
        $modelLabel = trim(strip_tags((string) static::getResource()::getModelLabel()));

        if (filled($title) && ! str($title)->lower()->exactly(str($modelLabel)->lower())) {
            return $title;
        }

        foreach ($this->getTable()->getVisibleColumns() as $column) {
            $value = $this->formatCardViewField($column, $record);

            if (filled($value)) {
                return $value;
            }
        }

        return $title ?: (string) $record->getKey();
    }

    public function isListView(): bool
    {
        return $this->viewType === 'list';
    }

    public function isTableView(): bool
    {
        return $this->isListView();
    }

    public function isCardView(): bool
    {
        return $this->viewType === 'card';
    }

    public function isKanbanView(): bool
    {
        return $this->viewType === 'kanban' && $this->hasKanbanView();
    }

    protected function getDefaultViewType(): string
    {
        return 'list';
    }

    protected function getSupportedViewTypes(): array
    {
        $viewTypes = ['list', 'card'];

        if ($this->hasKanbanView()) {
            $viewTypes[] = 'kanban';
        }

        return $viewTypes;
    }

    protected function hasKanbanView(): bool
    {
        return filled($this->getKanbanStatusField()) && (count($this->getKanbanStatuses()) > 0);
    }

    protected function getKanbanStatusField(): ?string
    {
        return null;
    }

    protected function getKanbanStatuses(): array
    {
        return [];
    }

    protected function mutateKanbanRecordStatus(Model $record, string $status): void
    {
    }

    public function getKanbanColumnClasses(string $accent): string
    {
        return match ($accent) {
            'blue' => 'ring-1 ring-sky-200/80 dark:ring-sky-900/60',
            'amber' => 'ring-1 ring-amber-200/80 dark:ring-amber-900/60',
            'emerald' => 'ring-1 ring-emerald-200/80 dark:ring-emerald-900/60',
            'rose' => 'ring-1 ring-rose-200/80 dark:ring-rose-900/60',
            'teal' => 'ring-1 ring-teal-200/80 dark:ring-teal-900/60',
            default => 'ring-1 ring-slate-200/90 dark:ring-slate-800/80',
        };
    }

    protected function getViewTypeLabel(string $viewType): string
    {
        return match ($viewType) {
            'card' => 'Cards',
            'kanban' => 'Kanban',
            default => 'List',
        };
    }

    protected function getViewTypeIcon(string $viewType): string
    {
        return match ($viewType) {
            'card' => 'heroicon-m-squares-2x2',
            'kanban' => 'heroicon-m-view-columns',
            default => 'heroicon-m-list-bullet',
        };
    }

    protected function normalizeViewType(?string $viewType): ?string
    {
        $viewType = match ($viewType) {
            'table' => 'list',
            'cards' => 'card',
            default => $viewType,
        };

        if (! is_string($viewType)) {
            return null;
        }

        return in_array($viewType, $this->getSupportedViewTypes(), true) ? $viewType : null;
    }

    protected function resolveInitialViewType(): string
    {
        $requestedViewType = $this->normalizeViewType(request()->query('view_type'));

        if ($requestedViewType) {
            return $requestedViewType;
        }

        $storedViewType = $this->normalizeViewType(data_get($this->getStoredTablePreferences(), 'view_type'));

        return $storedViewType ?? $this->getDefaultViewType();
    }

    protected function formatCardViewField(Column $column, Model $record): ?string
    {
        $column->record($record);

        $state = $column->getState();

        if (is_array($state)) {
            $state = collect($state)
                ->filter(fn ($item): bool => filled($item))
                ->implode(', ');
        }

        if (is_bool($state)) {
            $state = $state ? 'Yes' : 'No';
        }

        if (blank($state)) {
            return null;
        }

        if (method_exists($column, 'formatState')) {
            return trim(strip_tags((string) $column->formatState($state)));
        }

        return trim(strip_tags((string) $state));
    }

    protected function getCardViewFieldLimit(): int
    {
        return 6;
    }

    protected function getPersistentViewQueryParameters(): array
    {
        return collect([
            'activeTab' => $this->activeTab ?? null,
            'tableFilters' => $this->tableFilters ?? null,
            'tableGrouping' => $this->tableGrouping ?? null,
            'tableGroupingDirection' => $this->tableGroupingDirection ?? null,
            'tableSearch' => $this->tableSearch ?? null,
            'tableSortColumn' => $this->tableSortColumn ?? null,
            'tableSortDirection' => $this->tableSortDirection ?? null,
        ])
            ->reject(fn ($value): bool => $value === null || $value === '' || $value === [])
            ->all();
    }

    protected function getAdditionalViewTypeQueryParameters(): array
    {
        return [];
    }

    protected function getStoredTablePreferences(): array
    {
        $state = Setting::query()
            ->where('module', 'ui_preferences')
            ->where('key', $this->getTablePreferenceKey())
            ->value('value');

        return is_array($state) ? $state : [];
    }

    protected function saveTablePreferences(array $preferences): void
    {
        $state = array_merge($this->getStoredTablePreferences(), $preferences);

        Setting::query()->updateOrCreate(
            [
                'module' => 'ui_preferences',
                'key' => $this->getTablePreferenceKey(),
            ],
            [
                'value' => $state,
                'description' => 'Per-user saved table column visibility preferences.',
                'is_sensitive' => false,
            ],
        );
    }
}