<x-filament-panels::page>
    <div class="grid gap-6">
        <section class="app-surface-panel rounded-[28px] border p-6 shadow-sm">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500 dark:text-slate-400">Homepage</p>
                    <h2 class="text-3xl font-semibold tracking-tight text-slate-950 dark:text-slate-50">My Tasks</h2>
                    <p class="max-w-2xl text-sm text-slate-600 dark:text-slate-300">Your assigned work, due-soon items, and quick actions all stay on the main admin panel.</p>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="app-surface-elevated rounded-2xl px-4 py-3 text-center">
                        <div class="text-2xl font-semibold text-slate-950 dark:text-slate-50">{{ number_format($summary['open'] ?? 0) }}</div>
                        <div class="text-xs uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Open</div>
                    </div>
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-center dark:border-rose-900/60 dark:bg-rose-950/20">
                        <div class="text-2xl font-semibold text-rose-700 dark:text-rose-300">{{ number_format($summary['overdue'] ?? 0) }}</div>
                        <div class="text-xs uppercase tracking-[0.2em] text-rose-500 dark:text-rose-300">Overdue</div>
                    </div>
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-center dark:border-amber-900/60 dark:bg-amber-950/20">
                        <div class="text-2xl font-semibold text-amber-700 dark:text-amber-300">{{ number_format($summary['due_soon'] ?? 0) }}</div>
                        <div class="text-xs uppercase tracking-[0.2em] text-amber-600 dark:text-amber-300">Due Soon</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
            <div class="app-surface-panel rounded-[28px] border shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-200/80 px-6 py-4 dark:border-slate-800">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950 dark:text-slate-50">Assigned To Me</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Sorted by urgency and due date.</p>
                    </div>

                    <a href="{{ \App\Filament\Resources\TaskResource::getUrl('index', ['view_type' => 'kanban']) }}" class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-950 dark:border-slate-700 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:text-white">
                        Open board
                    </a>
                </div>

                @if (count($tasks))
                    <div class="divide-y divide-slate-200/80 dark:divide-slate-800">
                        @foreach ($tasks as $task)
                            <article class="flex flex-col gap-4 px-6 py-5 lg:flex-row lg:items-start lg:justify-between {{ $this->getTaskTone($task) }}">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="text-base font-semibold text-slate-950 dark:text-slate-50">{{ $task['title'] }}</h4>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $this->getStatusClasses($task['status']) }}">
                                            {{ str($task['status'])->replace('_', ' ')->headline() }}
                                        </span>
                                        @if (filled($task['priority']))
                                            <span class="app-surface-elevated inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-200">
                                                {{ $task['priority'] }}
                                            </span>
                                        @endif
                                    </div>

                                    @if (filled($task['description']))
                                        <p class="max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $task['description'] }}</p>
                                    @endif

                                    <div class="flex flex-wrap gap-3 text-xs font-medium uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                                        <span>{{ $this->formatDueDate($task['due_date']) }}</span>

                                        @if ($task['is_overdue'])
                                            <span class="text-rose-600 dark:text-rose-300">Needs attention</span>
                                        @elseif ($task['is_due_soon'])
                                            <span class="text-amber-600 dark:text-amber-300">Due soon</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex shrink-0 gap-2">
                                    <a href="{{ \App\Filament\Resources\TaskResource::getUrl('index', ['view_type' => 'list']) }}" class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-950 dark:border-slate-700 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:text-white">
                                        View
                                    </a>
                                    <a href="{{ \App\Filament\Resources\TaskResource::getUrl('index', ['view_type' => 'list']) }}" class="inline-flex items-center rounded-full bg-primary-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-primary-500">
                                        Edit
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="px-6 py-14 text-center">
                        <p class="text-lg font-semibold text-slate-900 dark:text-slate-50">You're clear for now.</p>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No open tasks are currently assigned to your account.</p>
                    </div>
                @endif
            </div>

            <aside class="app-surface-panel space-y-4 rounded-[28px] border p-6 shadow-sm">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950 dark:text-slate-50">Quick Actions</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Jump straight into your task workflow.</p>
                </div>

                <div class="space-y-3">
                    @foreach ($quickActions as $action)
                        <a href="{{ $action['url'] }}" class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-950 dark:border-slate-700 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:bg-slate-800 dark:hover:text-white">
                            <span>{{ $action['label'] }}</span>
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    @endforeach
                </div>
            </aside>
        </section>
    </div>
</x-filament-panels::page>