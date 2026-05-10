<?php

namespace App\Repositories\Operations;

use App\Models\Booking;
use App\Models\WorkOrder;
use App\Models\WorkOrderMaterial;
use App\Models\WorkOrderPhoto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

    public function findForShow(WorkOrder $workOrder): WorkOrder
    {
        return $workOrder->load(['materials', 'bookings', 'photos']);
    }

    public function create(array $payload): WorkOrder
    {
        return WorkOrder::query()->create($payload);
    }

    public function update(WorkOrder $workOrder, array $payload): WorkOrder
    {
        $workOrder->update($payload);

        return $workOrder->refresh();
    }

    public function delete(WorkOrder $workOrder): void
    {
        $workOrder->delete();
    }

    public function assignContractor(WorkOrder $workOrder, array $payload): WorkOrder
    {
        $workOrder->update([
            'assigned_contractor_id' => $payload['assigned_contractor_id'],
            'assigned_contractor' => $payload['assigned_contractor'] ?? null,
            'status' => $workOrder->status === 'new' ? 'scheduled' : $workOrder->status,
        ]);

        return $workOrder->refresh();
    }

    public function addMaterial(WorkOrder $workOrder, array $payload): WorkOrderMaterial
    {
        return WorkOrderMaterial::query()->create([
            'work_order_id' => $workOrder->id,
            'product_id' => $payload['product_id'],
            'product_name' => $payload['product_name'] ?? null,
            'quantity' => $payload['quantity'],
            'unit_cost' => $payload['unit_cost'] ?? null,
            'total_cost' => $payload['total_cost'] ?? null,
            'source' => $payload['source'] ?? 'inventory',
            'is_billable' => $payload['is_billable'] ?? true,
            'created_by' => $payload['actor_id'] ?? null,
            'updated_by' => $payload['actor_id'] ?? null,
        ]);
    }

    public function addBooking(WorkOrder $workOrder, array $payload): Booking
    {
        return Booking::query()->create([
            'work_order_id' => $workOrder->id,
            'booking_date' => $payload['booking_date'],
            'start_time' => $payload['start_time'] ?? null,
            'end_time' => $payload['end_time'] ?? null,
            'address' => $payload['address'] ?? null,
            'duration_minutes' => $payload['duration_minutes'] ?? null,
            'notes' => $payload['notes'] ?? null,
            'created_by' => $payload['actor_id'] ?? null,
            'updated_by' => $payload['actor_id'] ?? null,
        ]);
    }

    public function addPhoto(WorkOrder $workOrder, array $payload): WorkOrderPhoto
    {
        return WorkOrderPhoto::query()->create([
            'work_order_id' => $workOrder->id,
            'photo_url' => $payload['photo_url'],
            'description' => $payload['description'] ?? null,
            'uploaded_by' => $payload['actor_id'] ?? null,
            'created_by' => $payload['actor_id'] ?? null,
            'updated_by' => $payload['actor_id'] ?? null,
        ]);
    }
}
