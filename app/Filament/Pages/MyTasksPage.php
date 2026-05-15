<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\Task;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class MyTasksPage extends Page
{
    protected static ?string $title = 'My Tasks';

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'My Tasks';

    protected static ?int $navigationSort = 0;

    protected static ?string $slug = 'my-tasks';

    protected static string $view = 'filament.pages.my-tasks-page';

    public array $summary = [];

    public array $quickActions = [];

    public array $tasks = [];

    public function mount(): void
    {
        $user = auth()->user();

        abort_unless($user, 403);

        $openStatuses = ['pending', 'in_progress'];

        $taskQuery = Task::query()
            ->where('assigned_to', $user->id)
            ->whereIn('status', $openStatuses);

        $taskItems = (clone $taskQuery)
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->limit(8)
            ->get();

        $this->summary = [
            'open' => (clone $taskQuery)->count(),
            'overdue' => (clone $taskQuery)->where('due_date', '<', now())->count(),
            'due_soon' => (clone $taskQuery)->whereBetween('due_date', [now(), now()->addDays(7)])->count(),
        ];

        $this->tasks = $taskItems
            ->map(function (Task $task): array {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'priority' => $task->priority,
                    'status' => $task->status,
                    'due_date' => optional($task->due_date)?->toIso8601String(),
                    'is_overdue' => filled($task->due_date) && $task->due_date->isPast(),
                    'is_due_soon' => filled($task->due_date) && $task->due_date->isFuture() && $task->due_date->lte(now()->addDays(3)),
                ];
            })
            ->values()
            ->all();

        $this->quickActions = [
            ['label' => 'Open Task Board', 'url' => TaskResource::getUrl('index', ['view_type' => 'kanban'])],
            ['label' => 'List View', 'url' => TaskResource::getUrl('index', ['view_type' => 'list'])],
            ['label' => 'Task Table', 'url' => TaskResource::getUrl('index')],
        ];
    }

    public function getHeading(): string | Htmlable
    {
        return 'My Tasks';
    }

    public function formatDueDate(?string $value): string
    {
        if (! $value) {
            return 'No due date';
        }

        return now()->parse($value)->format('M j, Y g:i A');
    }

    public function getStatusClasses(string $status): string
    {
        return match ($status) {
            'pending' => 'app-surface-elevated border border-amber-200 text-amber-700 dark:border-amber-800 dark:text-amber-300',
            'in_progress' => 'app-surface-elevated border border-sky-200 text-sky-700 dark:border-sky-800 dark:text-sky-300',
            'completed' => 'app-surface-elevated border border-emerald-200 text-emerald-700 dark:border-emerald-800 dark:text-emerald-300',
            default => 'app-surface-elevated border border-slate-200 text-slate-700 dark:border-slate-700 dark:text-slate-200',
        };
    }

    public function getTaskTone(array $task): string
    {
        if ($task['is_overdue']) {
            return 'border-rose-200/80 bg-rose-50/60 dark:border-rose-900/60 dark:bg-rose-950/20';
        }

        if ($task['is_due_soon']) {
            return 'border-amber-200/80 bg-amber-50/70 dark:border-amber-900/60 dark:bg-amber-950/20';
        }

        return 'border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-900';
    }
}