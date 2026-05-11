<?php

namespace App\Filament\Pages;

use App\Models\Job;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OperationsDashboard extends Page
{
    protected static ?string $title = 'Operations Dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.operations-dashboard';

    public string $startDate;

    public string $endDate;

    public string $upcomingStartDate;

    public string $upcomingEndDate;

    public array $heroStats = [];

    public array $metricCards = [];

    public array $trendCards = [];

    public array $statusBreakdown = [];

    public array $upcomingRevenue = [];

    public array $upcomingJobs = [];

    public array $materialHighlights = [];

    public array $topMaterialJobs = [];

    public array $riskLists = [];

    public array $contractorSnapshot = [];

    public array $closeoutSummary = [];

    public array $closeoutItems = [];

    public array $recentWorkOrders = [];

    public function mount(): void
    {
        $this->startDate = (string) request()->query('start_date', now()->subDays(29)->toDateString());
        $this->endDate = (string) request()->query('end_date', now()->toDateString());

        if (! $this->isValidDate($this->startDate)) {
            $this->startDate = now()->subDays(29)->toDateString();
        }

        if (! $this->isValidDate($this->endDate)) {
            $this->endDate = now()->toDateString();
        }

        if ($this->startDate > $this->endDate) {
            [$this->startDate, $this->endDate] = [$this->endDate, $this->startDate];
        }

        $this->upcomingStartDate = now()->toDateString();
        $this->upcomingEndDate = now()->addDays(30)->toDateString();

        $this->hydrateDashboard();
    }

    public function formatMoney(float | int | string | null $amount): string
    {
        return '$' . number_format((float) ($amount ?? 0), 2);
    }

    public function formatCount(float | int $value, int $precision = 0): string
    {
        return number_format($value, $precision);
    }

    public function getRangeLabel(): string
    {
        return Carbon::parse($this->startDate)->format('M j, Y') . ' - ' . Carbon::parse($this->endDate)->format('M j, Y');
    }

    public function getUpcomingLabel(): string
    {
        return Carbon::parse($this->upcomingStartDate)->format('M j') . ' - ' . Carbon::parse($this->upcomingEndDate)->format('M j, Y');
    }

    public function getHeading(): string | Htmlable
    {
        return 'Operations Dashboard';
    }

    public function getStatusLabel(string $status): string
    {
        return Job::statusOptions()[Job::normalizeStatus($status)] ?? str($status)->headline()->toString();
    }

    protected function hydrateDashboard(): void
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();
        $rangeDays = max($start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1, 1);
        $previousEnd = $start->copy()->subDay()->endOfDay();
        $previousStart = $previousEnd->copy()->subDays($rangeDays - 1)->startOfDay();
        $today = now()->toDateString();
        $upcomingEnd = Carbon::parse($this->upcomingEndDate)->endOfDay()->toDateString();
        $upcomingDays = max(Carbon::parse($this->upcomingStartDate)->diffInDays(Carbon::parse($this->upcomingEndDate)) + 1, 1);
        $previousUpcomingEnd = Carbon::parse($this->upcomingStartDate)->subDay()->toDateString();
        $previousUpcomingStart = Carbon::parse($this->upcomingStartDate)->subDays($upcomingDays)->toDateString();
        $activeStatuses = ['new', 'scheduled', 'in_progress', 'rework'];
        $executionStatuses = ['scheduled', 'in_progress', 'rework'];

        $openWorkOrders = WorkOrder::query()
            ->whereIn('status', $activeStatuses)
            ->count();

        $overdueWorkOrders = WorkOrder::query()
            ->whereIn('status', $activeStatuses)
            ->whereDate('scheduled_date', '<', $today)
            ->count();

        $reworkLast30Days = (int) DB::table('work_order_status_history')
            ->where('new_status', 'rework')
            ->whereBetween('changed_at', [$start, $end])
            ->distinct('work_order_id')
            ->count('work_order_id');

        $previousRework = (int) DB::table('work_order_status_history')
            ->where('new_status', 'rework')
            ->whereBetween('changed_at', [$previousStart, $previousEnd])
            ->distinct('work_order_id')
            ->count('work_order_id');

        $upcomingRevenueBase = DB::table('work_orders')
            ->leftJoin('invoices', 'invoices.id', '=', 'work_orders.invoice_id')
            ->whereBetween('work_orders.scheduled_date', [$today, $upcomingEnd])
            ->whereNotIn('work_orders.status', ['cancelled', 'archived'])
            ->select([
                'work_orders.id',
                'work_orders.work_order_number',
                'work_orders.customer_name',
                'work_orders.service_name',
                'work_orders.scheduled_date',
                'work_orders.status',
                'work_orders.invoice_number',
                'invoices.total_amount',
                'invoices.amount_due',
            ])
            ->orderBy('work_orders.scheduled_date')
            ->get();

        $upcomingRevenueValue = (float) $upcomingRevenueBase->sum(fn ($job): float => (float) ($job->total_amount ?? 0));

        $previousUpcomingRevenueValue = (float) DB::table('work_orders')
            ->leftJoin('invoices', 'invoices.id', '=', 'work_orders.invoice_id')
            ->whereBetween('work_orders.scheduled_date', [$previousUpcomingStart, $previousUpcomingEnd])
            ->whereNotIn('work_orders.status', ['cancelled', 'archived'])
            ->sum('invoices.total_amount');

        $materialRows = DB::table('work_order_materials')
            ->whereBetween('created_at', [$start, $end])
            ->get(['work_order_id', 'source', 'is_billable', 'total_cost']);

        $previousMaterialSpend = (float) DB::table('work_order_materials')
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->sum('total_cost');

        $materialSpend = (float) $materialRows->sum(fn ($row): float => (float) ($row->total_cost ?? 0));

        $unassignedScheduled = WorkOrder::query()
            ->whereIn('status', $executionStatuses)
            ->whereNull('assigned_contractor_id')
            ->count();

        $dueToday = WorkOrder::query()
            ->whereIn('status', $executionStatuses)
            ->whereDate('scheduled_date', $today)
            ->count();

        $completedThisWeek = WorkOrder::query()
            ->where('status', 'completed')
            ->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $completedInRange = WorkOrder::query()
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$start, $end])
            ->count();

        $previousCompletedInRange = WorkOrder::query()
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$previousStart, $previousEnd])
            ->count();

        $scheduledWithoutInvoice = WorkOrder::query()
            ->whereBetween('scheduled_date', [$today, $upcomingEnd])
            ->whereIn('status', $executionStatuses)
            ->whereNull('invoice_id')
            ->count();

        $jobsMissingMaterials = WorkOrder::query()
            ->whereIn('status', ['scheduled', 'in_progress', 'completed', 'rework'])
            ->doesntHave('materials')
            ->count();

        $jobsMissingBookings = WorkOrder::query()
            ->whereIn('status', $executionStatuses)
            ->doesntHave('bookings')
            ->count();

        $upcomingReceivables = (float) $upcomingRevenueBase->sum(fn ($job): float => (float) ($job->amount_due ?? 0));
        $billableMaterialSpend = (float) $materialRows->where('is_billable', true)->sum(fn ($row): float => (float) ($row->total_cost ?? 0));
        $nonBillableMaterialSpend = max($materialSpend - $billableMaterialSpend, 0);

        $this->heroStats = [
            [
                'label' => 'Open Work Orders',
                'value' => $this->formatCount($openWorkOrders),
                'note' => $this->formatCount($overdueWorkOrders) . ' overdue right now',
                'tone' => 'slate',
                'href' => $this->jobsUrl(),
            ],
            [
                'label' => 'Rework In Range',
                'value' => $this->formatCount($reworkLast30Days),
                'note' => 'Jobs moved into rework during ' . strtolower($this->getRangeLabel()),
                'tone' => 'rose',
                'href' => $this->jobsUrl(status: 'rework'),
            ],
            [
                'label' => 'Upcoming Revenue',
                'value' => $this->formatMoney($upcomingRevenueValue),
                'note' => $this->formatCount($scheduledWithoutInvoice) . ' scheduled jobs missing invoices',
                'tone' => 'emerald',
                'href' => $this->invoicesUrl(),
            ],
            [
                'label' => 'Material Spend',
                'value' => $this->formatMoney($materialSpend),
                'note' => $this->formatMoney($billableMaterialSpend) . ' billable in selected range',
                'tone' => 'amber',
                'href' => $this->jobsUrl(),
            ],
        ];

        $this->metricCards = [
            [
                'label' => 'Due Today',
                'value' => $this->formatCount($dueToday),
                'meta' => 'Scheduled work orders due on today\'s board',
                'href' => '/admin/schedulings',
            ],
            [
                'label' => 'Completed This Week',
                'value' => $this->formatCount($completedThisWeek),
                'meta' => 'Finished jobs closed during the current week',
                'href' => $this->jobsUrl(status: 'completed'),
            ],
            [
                'label' => 'Unassigned Scheduled',
                'value' => $this->formatCount($unassignedScheduled),
                'meta' => 'Jobs in execution states without a contractor',
                'href' => $this->jobsUrl(status: 'scheduled'),
            ],
            [
                'label' => 'Upcoming Receivables',
                'value' => $this->formatMoney($upcomingReceivables),
                'meta' => 'Outstanding amount tied to scheduled invoiced work',
                'href' => $this->invoicesUrl(status: 'partial'),
            ],
            [
                'label' => 'Missing Materials',
                'value' => $this->formatCount($jobsMissingMaterials),
                'meta' => 'Execution and completed jobs without material records',
                'href' => $this->jobsUrl(),
            ],
            [
                'label' => 'Missing Bookings',
                'value' => $this->formatCount($jobsMissingBookings),
                'meta' => 'Scheduled or active jobs missing a booking record',
                'href' => '/admin/schedulings',
            ],
        ];

        $this->trendCards = [
            [
                'label' => 'Completed Jobs',
                'current' => $completedInRange,
                'current_label' => $this->formatCount($completedInRange),
                'previous_label' => $this->formatCount($previousCompletedInRange),
                'meta' => 'Compared with the prior ' . $rangeDays . '-day window',
                ...$this->buildDelta($completedInRange, $previousCompletedInRange),
                'href' => $this->jobsUrl(status: 'completed'),
            ],
            [
                'label' => 'Rework Entries',
                'current' => $reworkLast30Days,
                'current_label' => $this->formatCount($reworkLast30Days),
                'previous_label' => $this->formatCount($previousRework),
                'meta' => 'Status changes into rework vs the previous period',
                ...$this->buildDelta($reworkLast30Days, $previousRework),
                'href' => $this->jobsUrl(status: 'rework'),
            ],
            [
                'label' => 'Material Spend',
                'current' => $materialSpend,
                'current_label' => $this->formatMoney($materialSpend),
                'previous_label' => $this->formatMoney($previousMaterialSpend),
                'meta' => 'Recorded material costs vs the previous period',
                ...$this->buildDelta($materialSpend, $previousMaterialSpend),
                'href' => $this->jobsUrl(),
            ],
            [
                'label' => 'Upcoming Revenue',
                'current' => $upcomingRevenueValue,
                'current_label' => $this->formatMoney($upcomingRevenueValue),
                'previous_label' => $this->formatMoney($previousUpcomingRevenueValue),
                'meta' => 'Next 30 days vs the prior 30 scheduled days',
                ...$this->buildDelta($upcomingRevenueValue, $previousUpcomingRevenueValue),
                'href' => $this->invoicesUrl(),
            ],
        ];

        $statusCounts = WorkOrder::query()
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->whereIn('status', array_keys(Job::kanbanStatuses()))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $maxStatusCount = max((int) $statusCounts->max(), 1);

        $this->statusBreakdown = collect(Job::kanbanStatuses())
            ->map(function (array $config, string $status) use ($statusCounts, $maxStatusCount): array {
                $count = (int) ($statusCounts[$status] ?? 0);

                return [
                    'label' => $config['label'],
                    'description' => $config['description'],
                    'value' => $count,
                    'width' => $count > 0 ? (int) round(($count / $maxStatusCount) * 100) : 0,
                    'tone' => $config['accent'],
                ];
            })
            ->values()
            ->all();

        $upcomingWeeks = $upcomingRevenueBase
            ->groupBy(function ($job): string {
                return Carbon::parse($job->scheduled_date)->startOfWeek()->format('M j');
            })
            ->map(function (Collection $jobs, string $week): array {
                return [
                    'label' => $week,
                    'amount' => (float) $jobs->sum(fn ($job): float => (float) ($job->total_amount ?? 0)),
                    'jobs' => $jobs->count(),
                ];
            })
            ->sortByDesc('amount')
            ->values();

        $maxUpcomingRevenue = max((float) $upcomingWeeks->max('amount'), 1);

        $this->upcomingRevenue = $upcomingWeeks
            ->map(fn (array $row): array => [
                ...$row,
                'width' => $row['amount'] > 0 ? (int) round(($row['amount'] / $maxUpcomingRevenue) * 100) : 0,
            ])
            ->all();

        $this->upcomingJobs = $upcomingRevenueBase
            ->sortByDesc(fn ($job): float => (float) ($job->total_amount ?? 0))
            ->take(6)
            ->map(fn ($job): array => [
                'work_order_number' => $job->work_order_number,
                'customer_name' => $job->customer_name ?: 'Unknown customer',
                'service_name' => $job->service_name ?: 'Service not set',
                'scheduled_date' => optional(Carbon::parse($job->scheduled_date))->format('M j, Y'),
                'invoice_number' => $job->invoice_number,
                'amount' => (float) ($job->total_amount ?? 0),
                'status' => $this->getStatusLabel((string) $job->status),
                'href' => $this->jobsUrl(search: $job->work_order_number),
                'invoice_href' => $job->invoice_number ? $this->invoicesUrl(search: $job->invoice_number) : null,
            ])
            ->values()
            ->all();

        $this->materialHighlights = [
            [
                'label' => 'Billable Material Spend',
                'value' => $this->formatMoney($billableMaterialSpend),
                'meta' => $materialSpend > 0 ? round(($billableMaterialSpend / $materialSpend) * 100) . '% of range total' : 'No material spend in range',
            ],
            [
                'label' => 'Non-Billable Material Spend',
                'value' => $this->formatMoney($nonBillableMaterialSpend),
                'meta' => $materialSpend > 0 ? round(($nonBillableMaterialSpend / $materialSpend) * 100) . '% of range total' : 'No material spend in range',
            ],
        ];

        $materialSourceRows = $materialRows
            ->groupBy('source')
            ->map(fn (Collection $rows, string $source): array => [
                'label' => str($source)->headline()->toString(),
                'amount' => (float) $rows->sum(fn ($row): float => (float) ($row->total_cost ?? 0)),
                'count' => $rows->count(),
            ])
            ->sortByDesc('amount')
            ->values();

        $maxMaterialSource = max((float) $materialSourceRows->max('amount'), 1);

        $this->materialHighlights = [
            ...$this->materialHighlights,
            ...$materialSourceRows
                ->map(fn (array $row): array => [
                    'label' => $row['label'] . ' Source',
                    'value' => $this->formatMoney($row['amount']),
                    'meta' => $this->formatCount($row['count']) . ' lines recorded',
                    'width' => $row['amount'] > 0 ? (int) round(($row['amount'] / $maxMaterialSource) * 100) : 0,
                ])
                ->take(3)
                ->all(),
        ];

        $topMaterialRows = DB::table('work_order_materials')
            ->join('work_orders', 'work_orders.id', '=', 'work_order_materials.work_order_id')
            ->whereBetween('work_order_materials.created_at', [$start, $end])
            ->groupBy('work_orders.id', 'work_orders.work_order_number', 'work_orders.customer_name', 'work_orders.service_name')
            ->orderByDesc(DB::raw('SUM(COALESCE(work_order_materials.total_cost, 0))'))
            ->limit(6)
            ->get([
                'work_orders.id',
                'work_orders.work_order_number',
                'work_orders.customer_name',
                'work_orders.service_name',
                DB::raw('SUM(COALESCE(work_order_materials.total_cost, 0)) as total_cost'),
                DB::raw('SUM(CASE WHEN work_order_materials.is_billable THEN COALESCE(work_order_materials.total_cost, 0) ELSE 0 END) as billable_cost'),
                DB::raw('COUNT(work_order_materials.id) as material_lines'),
            ]);

        $maxMaterialCost = max((float) $topMaterialRows->max('total_cost'), 1);

        $this->topMaterialJobs = $topMaterialRows
            ->map(fn ($row): array => [
                'work_order_number' => $row->work_order_number,
                'customer_name' => $row->customer_name ?: 'Unknown customer',
                'service_name' => $row->service_name ?: 'Service not set',
                'total_cost' => (float) $row->total_cost,
                'billable_cost' => (float) $row->billable_cost,
                'material_lines' => (int) $row->material_lines,
                'width' => $row->total_cost > 0 ? (int) round((((float) $row->total_cost) / $maxMaterialCost) * 100) : 0,
            ])
            ->all();

        $currentReworkJobs = WorkOrder::query()
            ->where('status', 'rework')
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get(['work_order_number', 'customer_name', 'service_name', 'assigned_contractor', 'updated_at']);

        $currentOverdueJobs = WorkOrder::query()
            ->whereIn('status', $activeStatuses)
            ->whereDate('scheduled_date', '<', $today)
            ->orderBy('scheduled_date')
            ->limit(6)
            ->get(['work_order_number', 'customer_name', 'service_name', 'assigned_contractor', 'scheduled_date']);

        $this->riskLists = [
            'rework' => $currentReworkJobs
                ->map(fn (WorkOrder $job): array => [
                    'title' => $job->work_order_number,
                    'subtitle' => trim(($job->customer_name ?: 'Unknown customer') . ' · ' . ($job->service_name ?: 'Service not set')),
                    'meta' => 'Updated ' . optional($job->updated_at)->diffForHumans(),
                    'value' => $job->assigned_contractor ?: 'Unassigned',
                    'href' => $this->jobsUrl(search: $job->work_order_number),
                ])
                ->all(),
            'overdue' => $currentOverdueJobs
                ->map(fn (WorkOrder $job): array => [
                    'title' => $job->work_order_number,
                    'subtitle' => trim(($job->customer_name ?: 'Unknown customer') . ' · ' . ($job->service_name ?: 'Service not set')),
                    'meta' => optional($job->scheduled_date)->format('M j, Y'),
                    'value' => $job->assigned_contractor ?: 'Unassigned',
                    'href' => $this->jobsUrl(search: $job->work_order_number),
                ])
                ->all(),
        ];

        $contractorRows = DB::table('work_orders')
            ->whereIn('status', $executionStatuses)
            ->selectRaw("COALESCE(assigned_contractor, 'Unassigned') as contractor_name")
            ->selectRaw('COUNT(*) as active_jobs')
            ->selectRaw("SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_jobs")
            ->selectRaw("SUM(CASE WHEN status = 'rework' THEN 1 ELSE 0 END) as rework_jobs")
            ->groupBy(DB::raw("COALESCE(assigned_contractor, 'Unassigned')"))
            ->orderByDesc('active_jobs')
            ->limit(6)
            ->get();

        $this->contractorSnapshot = $contractorRows
            ->map(fn ($row): array => [
                'name' => $row->contractor_name,
                'active_jobs' => (int) $row->active_jobs,
                'in_progress_jobs' => (int) $row->in_progress_jobs,
                'rework_jobs' => (int) $row->rework_jobs,
            ])
            ->all();

        $this->recentWorkOrders = WorkOrder::query()
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get([
                'work_order_number',
                'customer_name',
                'service_name',
                'assigned_contractor',
                'status',
                'scheduled_date',
                'invoice_number',
                'updated_at',
            ])
            ->map(fn (WorkOrder $job): array => [
                'work_order_number' => $job->work_order_number,
                'customer_name' => $job->customer_name ?: 'Unknown customer',
                'service_name' => $job->service_name ?: 'Service not set',
                'assigned_contractor' => $job->assigned_contractor ?: 'Unassigned',
                'status' => $this->getStatusLabel($job->status),
                'scheduled_date' => optional($job->scheduled_date)->format('M j, Y') ?: 'Not scheduled',
                'invoice_number' => $job->invoice_number ?: 'No invoice',
                'updated_at' => optional($job->updated_at)->diffForHumans(),
                'href' => $this->jobsUrl(search: $job->work_order_number),
                'invoice_href' => $job->invoice_number ? $this->invoicesUrl(search: $job->invoice_number) : null,
            ])
            ->all();

        $completedWorkOrders = WorkOrder::query()
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$start, $end])
            ->orderByDesc('completed_at')
            ->get(['id', 'work_order_number', 'customer_name', 'service_name', 'completed_at']);

        $completedIds = $completedWorkOrders->pluck('id');

        $photoCounts = $completedIds->isEmpty()
            ? collect()
            : DB::table('work_order_photos')
                ->whereIn('work_order_id', $completedIds)
                ->select('work_order_id', DB::raw('COUNT(*) as aggregate'))
                ->groupBy('work_order_id')
                ->pluck('aggregate', 'work_order_id');

        $documentCounts = $completedIds->isEmpty()
            ? collect()
            : DB::table('work_order_documents')
                ->whereIn('work_order_id', $completedIds)
                ->select('work_order_id', DB::raw('COUNT(*) as aggregate'))
                ->groupBy('work_order_id')
                ->pluck('aggregate', 'work_order_id');

        $qaRows = $completedIds->isEmpty()
            ? collect()
            : DB::table('tasks')
                ->where('category', 'operations')
                ->where('related_type', 'work_order')
                ->whereIn('related_work_order_id', $completedIds)
                ->where('title', 'like', 'Quality Review:%')
                ->select('related_work_order_id', 'status', 'completed_at')
                ->get();

        $qaGrouped = $qaRows->groupBy('related_work_order_id');

        $closeoutRows = $completedWorkOrders->map(function (WorkOrder $job) use ($photoCounts, $documentCounts, $qaGrouped): array {
            $photos = (int) ($photoCounts[$job->id] ?? 0);
            $documents = (int) ($documentCounts[$job->id] ?? 0);
            $qaTasks = $qaGrouped->get($job->id, collect());
            $qaCompleted = $qaTasks->contains(fn ($task): bool => ($task->status ?? null) === 'completed' || filled($task->completed_at ?? null));
            $qaOpen = $qaTasks->contains(fn ($task): bool => ! in_array($task->status ?? null, ['completed', 'cancelled'], true));
            $missing = collect([
                $photos > 0 ? null : 'photos',
                $documents > 0 ? null : 'documents',
                $qaCompleted ? null : 'QA review',
            ])->filter()->values()->all();

            return [
                'id' => $job->id,
                'work_order_number' => $job->work_order_number,
                'customer_name' => $job->customer_name ?: 'Unknown customer',
                'service_name' => $job->service_name ?: 'Service not set',
                'completed_at' => optional($job->completed_at)->format('M j, Y'),
                'photos' => $photos,
                'documents' => $documents,
                'qa_open' => $qaOpen,
                'qa_completed' => $qaCompleted,
                'is_complete' => $photos > 0 && $documents > 0 && $qaCompleted,
                'missing' => $missing,
                'href' => $this->jobsUrl(search: $job->work_order_number),
            ];
        });

        $closeoutComplete = $closeoutRows->where('is_complete', true)->count();
        $missingPhotos = $closeoutRows->filter(fn (array $row): bool => $row['photos'] === 0)->count();
        $missingDocuments = $closeoutRows->filter(fn (array $row): bool => $row['documents'] === 0)->count();
        $openQualityReviews = $closeoutRows->filter(fn (array $row): bool => $row['qa_open'] || ! $row['qa_completed'])->count();

        $this->closeoutSummary = [
            [
                'label' => 'Completed In Range',
                'value' => $this->formatCount($completedWorkOrders->count()),
                'meta' => 'Completed work orders in the selected reporting range',
                'href' => $this->jobsUrl(status: 'completed'),
            ],
            [
                'label' => 'Closeout Complete',
                'value' => $this->formatCount($closeoutComplete),
                'meta' => $completedWorkOrders->count() > 0 ? round(($closeoutComplete / $completedWorkOrders->count()) * 100) . '% with photos, docs, and QA complete' : 'No completed work orders in range',
                'href' => $this->jobsUrl(status: 'completed'),
            ],
            [
                'label' => 'Missing Photos',
                'value' => $this->formatCount($missingPhotos),
                'meta' => 'Completed jobs without photo evidence yet',
                'href' => $this->jobsUrl(status: 'completed'),
            ],
            [
                'label' => 'Missing Documents',
                'value' => $this->formatCount($missingDocuments),
                'meta' => 'Completed jobs without uploaded work-order documents',
                'href' => $this->jobsUrl(status: 'completed'),
            ],
            [
                'label' => 'Open QA Reviews',
                'value' => $this->formatCount($openQualityReviews),
                'meta' => 'Quality reviews still pending completion or missing',
                'href' => $this->tasksUrl(category: 'operations', status: 'pending'),
            ],
        ];

        $this->closeoutItems = $closeoutRows
            ->where('is_complete', false)
            ->take(6)
            ->values()
            ->all();
    }

    protected function jobsUrl(?string $status = null, ?string $search = null): string
    {
        $query = ['view_type' => 'table'];

        if ($status) {
            $query['tableFilters'] = ['status' => ['value' => $status]];
        }

        if ($search) {
            $query['tableSearch'] = $search;
        }

        return $this->adminUrl('jobs', $query);
    }

    protected function invoicesUrl(?string $status = null, ?string $search = null): string
    {
        $query = [];

        if ($status) {
            $query['tableFilters'] = ['status' => ['value' => $status]];
        }

        if ($search) {
            $query['tableSearch'] = $search;
        }

        return $this->adminUrl('invoices', $query);
    }

    protected function tasksUrl(?string $category = null, ?string $status = null): string
    {
        $filters = [];

        if ($category) {
            $filters['category'] = ['value' => $category];
        }

        if ($status) {
            $filters['status'] = ['value' => $status];
        }

        return $this->adminUrl('tasks', $filters ? ['tableFilters' => $filters] : []);
    }

    protected function adminUrl(string $path, array $query = []): string
    {
        $url = url('/admin/' . ltrim($path, '/'));

        return $query ? $url . '?' . http_build_query($query) : $url;
    }

    protected function buildDelta(float | int $current, float | int $previous): array
    {
        $delta = $current - $previous;

        if ($current == 0 && $previous == 0) {
            return [
                'delta_label' => 'No change vs previous period',
                'delta_tone' => 'slate',
                'delta_width' => 50,
                'previous_width' => 50,
            ];
        }

        if ($previous == 0) {
            return [
                'delta_label' => '+' . $this->formatCount($current, is_float($current) ? 2 : 0) . ' from zero baseline',
                'delta_tone' => 'emerald',
                'delta_width' => 100,
                'previous_width' => 0,
            ];
        }

        $percent = round((($current - $previous) / abs($previous)) * 100);
        $max = max(abs((float) $current), abs((float) $previous), 1);

        return [
            'delta_label' => ($percent > 0 ? '+' : '') . $percent . '% vs previous period',
            'delta_tone' => $delta > 0 ? 'emerald' : ($delta < 0 ? 'rose' : 'slate'),
            'delta_width' => (int) round((abs((float) $current) / $max) * 100),
            'previous_width' => (int) round((abs((float) $previous) / $max) * 100),
        ];
    }

    protected function isValidDate(string $value): bool
    {
        try {
            Carbon::parse($value);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
