<?php

namespace App\Repositories\Operations;

use App\Models\WorkOrder;
use Illuminate\Support\Collection;

class WorkOrderRepository
{
    public function dashboardStats(): array
    {
        return [
            'openWorkOrders' => WorkOrder::query()->whereNotIn('status', ['completed', 'cancelled', 'archived'])->count(),
            'dueToday' => WorkOrder::query()->whereDate('scheduled_date', now()->toDateString())->count(),
            'overdue' => WorkOrder::query()
                ->whereNotIn('status', ['completed', 'cancelled', 'archived'])
                ->whereDate('scheduled_date', '<', now()->toDateString())
                ->count(),
            'completedThisWeek' => WorkOrder::query()->where('status', 'completed')->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'pendingQualityReviews' => WorkOrder::query()->where('status', 'completed')->doesntHave('bookings')->count(),
            'unassigned' => WorkOrder::query()->whereNull('assigned_contractor_id')->count(),
            'activeOperationsTickets' => \App\Models\Ticket::query()->where('category', 'operations')->whereNotIn('status', ['resolved', 'closed'])->count(),
        ];
    }

    public function paginated(array $filters): LengthAwarePaginator
    {
        return WorkOrder::query()
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['assigned_contractor'] ?? null, fn ($q, $contractor) => $q->where('assigned_contractor', 'like', "%{$contractor}%"))
            ->when($filters['due_date'] ?? null, fn ($q, $date) => $q->whereDate('scheduled_date', $date))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();
    }

    public function recent(int $limit = 10): Collection
    {
        return WorkOrder::query()->orderByDesc('created_at')->limit($limit)->get();
    }
}
