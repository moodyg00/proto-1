<?php

namespace App\Filament\Resources\Pages;

use App\Models\Setting;
use Filament\Actions\Action as PageAction;
use Filament\Forms;
use Filament\Resources\Pages\ManageRecords;
use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

abstract class AppLabManageRecords extends ManageRecords
{
    protected array $savedTableColumnOrder = [];

    public function mount(): void
    {
        $this->primeSavedTableColumnPreferences();

        parent::mount();
    }

    public function updatedToggledTableColumns(): void
    {
        parent::updatedToggledTableColumns();

        if (! Auth::check()) {
            return;
        }

        $this->saveTablePreferences([
            'toggled_columns' => $this->toggledTableColumns,
            'column_order' => $this->savedTableColumnOrder,
        ]);
    }

    protected function primeSavedTableColumnPreferences(): void
    {
        if (! Auth::check()) {
            return;
        }

        $state = $this->getStoredTablePreferences();

        $savedColumns = data_get($state, 'toggled_columns');
        $savedColumnOrder = data_get($state, 'column_order');

        if (is_array($savedColumnOrder)) {
            $this->savedTableColumnOrder = array_values(array_filter($savedColumnOrder, 'is_string'));
        }

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
        return 'user:' . Auth::id() . ':table-columns:' . static::class;
    }

    public function table(Table $table): Table
    {
        $table = parent::table($table);

        $this->applySavedTableColumnOrder($table);

        return $table;
    }

    protected function getHeaderActions(): array
    {
        return [
            ...parent::getHeaderActions(),
            $this->makeReorderColumnsAction(),
        ];
    }

    protected function makeReorderColumnsAction(): PageAction
    {
        return PageAction::make('reorderColumns')
            ->label('Reorder Columns')
            ->icon('heroicon-o-bars-3-bottom-left')
            ->modalHeading('Reorder table columns')
            ->modalSubmitActionLabel('Save order')
            ->fillForm(fn (): array => [
                'columns' => $this->getCurrentTableColumnsForOrdering(),
            ])
            ->form([
                Forms\Components\Repeater::make('columns')
                    ->label('Visible order')
                    ->schema([
                        Forms\Components\Hidden::make('key'),
                        Forms\Components\TextInput::make('label')
                            ->disabled(),
                    ])
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable()
                    ->columnSpanFull(),
            ])
            ->action(function (array $data): void {
                $orderedColumns = collect($data['columns'] ?? [])
                    ->pluck('key')
                    ->filter(fn ($key): bool => is_string($key) && filled($key))
                    ->values()
                    ->all();

                $this->savedTableColumnOrder = $orderedColumns;

                $this->saveTablePreferences([
                    'toggled_columns' => $this->toggledTableColumns,
                    'column_order' => $this->savedTableColumnOrder,
                ]);

                $this->resetTable();
            });
    }

    protected function getCurrentTableColumnsForOrdering(): array
    {
        return collect($this->getTable()->getColumns())
            ->map(fn (Column $column, string $name): array => [
                'key' => $name,
                'label' => trim(strip_tags((string) $column->getLabel())),
            ])
            ->values()
            ->all();
    }

    protected function applySavedTableColumnOrder(Table $table): void
    {
        if (! $this->canPersistColumnOrder($table)) {
            return;
        }

        if ($this->savedTableColumnOrder === []) {
            return;
        }

        $columns = $table->getColumns();

        if ($columns === []) {
            return;
        }

        $orderedColumns = [];

        foreach ($this->savedTableColumnOrder as $columnName) {
            if (! array_key_exists($columnName, $columns)) {
                continue;
            }

            $orderedColumns[] = $columns[$columnName];
            unset($columns[$columnName]);
        }

        $table->columns([
            ...$orderedColumns,
            ...array_values($columns),
        ]);
    }

    protected function canPersistColumnOrder(Table $table): bool
    {
        return (! $table->hasColumnsLayout())
            && (! $table->hasColumnGroups())
            && (count($table->getColumns()) > 1);
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
        Setting::query()->updateOrCreate(
            [
                'module' => 'ui_preferences',
                'key' => $this->getTablePreferenceKey(),
            ],
            [
                'value' => $preferences,
                'description' => 'Per-user saved table column visibility and order preferences.',
                'is_sensitive' => false,
            ],
        );
    }
}