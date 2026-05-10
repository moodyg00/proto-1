<?php

namespace App\Services\Operations;

use App\Models\ChangeLog;
use App\Models\Contact;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkOrder;
use App\Repositories\Operations\WorkOrderRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class WorkOrderService
{
    public function __construct(private readonly WorkOrderRepository $repository)
    {
    }

    public function dashboardPayload(): array
    {
        return [
            'stats' => $this->repository->dashboardStats(),
            'workOrders' => $this->repository->recent(15),
            'operationsTickets' => \App\Models\Ticket::query()->where('category', 'operations')->latest()->limit(10)->get(),
            'quickLinks' => [
                ['label' => 'Purchase Materials', 'href' => '/operations/work-orders?status=in_progress'],
                ['label' => 'Refunds & Credits', 'href' => '/accounting/credits'],
                ['label' => 'Manage Contractors', 'href' => '/crm/contacts?type=contractor'],
                ['label' => 'Manage Customers', 'href' => '/crm/contacts?type=customer'],
                ['label' => 'Services Catalog', 'href' => '/administration/services'],
                ['label' => 'View All Invoices', 'href' => '/accounting/invoices'],
            ],
        ];
    }

    public function indexPayload(array $filters): array
    {
        return [
            'workOrders' => $this->repository->paginated($filters),
            'filters' => $filters,
        ];
    }

    public function createFormPayload(): array
    {
        return [
            'customers' => Contact::query()->where('type', 'customer')->orderBy('name')->get(['id', 'name']),
            'services' => Service::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'contractors' => Contact::query()->where('type', 'contractor')->orderBy('name')->get(['id', 'name']),
        ];
    }

    public function showPayload(WorkOrder $workOrder): array
    {
        $order = $this->repository->findForShow($workOrder);

        return [
            'workOrder' => $order,
            'materials' => $order->materials,
            'bookings' => $order->bookings,
            'photos' => $order->photos,
            'missing' => [
                'assigned_contractor' => blank($order->assigned_contractor_id),
                'booking' => blank($order->booking_date),
                'materials' => $order->materials->isEmpty(),
                'invoice' => blank($order->invoice_number),
            ],
            'modalOptions' => [
                'contractors' => Contact::query()->where('type', 'contractor')->orderBy('name')->get(['id', 'name']),
                'products' => Product::query()->orderBy('name')->get(['id', 'name', 'unit_price']),
            ],
        ];
    }

    public function create(array $data, ?User $actor = null): WorkOrder
    {
        return DB::transaction(function () use ($data, $actor) {
            $customer = Contact::query()->findOrFail($data['contact_id']);
            $service = Service::query()->find($data['service_id']);
            $contractor = isset($data['assigned_contractor_id']) ? Contact::query()->find($data['assigned_contractor_id']) : null;

            $payload = [
                ...$data,
                'work_order_number' => $data['work_order_number'] ?? $this->nextWorkOrderNumber(),
                'customer_name' => $customer->name,
                'service_name' => $service?->name,
                'assigned_contractor' => $contractor?->name,
                'status' => ! empty($data['scheduled_date']) ? 'scheduled' : 'new',
                'created_by' => $actor?->id,
                'updated_by' => $actor?->id,
            ];

            $workOrder = $this->repository->create($payload);
            $this->log('work_orders', $workOrder->id, 'create', [], $workOrder->toArray(), $actor?->id);

            return $workOrder;
        });
    }

    public function update(WorkOrder $workOrder, array $data, ?User $actor = null): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $data, $actor) {
            $old = $workOrder->toArray();

            $customer = isset($data['contact_id']) ? Contact::query()->find($data['contact_id']) : null;
            $service = isset($data['service_id']) ? Service::query()->find($data['service_id']) : null;
            $contractor = isset($data['assigned_contractor_id']) ? Contact::query()->find($data['assigned_contractor_id']) : null;

            $payload = [
                ...$data,
                'customer_name' => $customer?->name ?? $workOrder->customer_name,
                'service_name' => $service?->name ?? $workOrder->service_name,
                'assigned_contractor' => $contractor?->name ?? $workOrder->assigned_contractor,
                'updated_by' => $actor?->id,
            ];

            $updated = $this->repository->update($workOrder, $payload);
            $this->log('work_orders', $updated->id, 'update', $old, $updated->toArray(), $actor?->id);

            return $updated;
        });
    }

    public function delete(WorkOrder $workOrder, ?User $actor = null): void
    {
        DB::transaction(function () use ($workOrder, $actor) {
            $old = $workOrder->toArray();
            $this->repository->delete($workOrder);
            $this->log('work_orders', $workOrder->id, 'delete', $old, [], $actor?->id);
        });
    }

    public function assignContractor(WorkOrder $workOrder, array $data, ?User $actor = null): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $data, $actor) {
            $old = $workOrder->toArray();
            $contractor = Contact::query()->findOrFail($data['assigned_contractor_id']);

            $updated = $this->repository->assignContractor($workOrder, [
                'assigned_contractor_id' => $contractor->id,
                'assigned_contractor' => $contractor->name,
            ]);

            $this->log('work_orders', $updated->id, 'automation', $old, $updated->toArray(), $actor?->id, [
                'source' => 'form automation',
                'rule' => 'assign contractor modal',
            ]);

            return $updated;
        });
    }

    public function addMaterial(WorkOrder $workOrder, array $data, ?User $actor = null): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $data, $actor) {
            $product = Product::query()->findOrFail($data['product_id']);
            $quantity = (float) $data['quantity'];
            $unitCost = (float) ($data['unit_cost'] ?? $product->unit_price ?? 0);

            $this->repository->addMaterial($workOrder, [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $quantity * $unitCost,
                'source' => $data['source'] ?? 'inventory',
                'is_billable' => Arr::get($data, 'is_billable', true),
                'actor_id' => $actor?->id,
            ]);

            $updated = $workOrder->refresh();
            $this->log('work_orders', $updated->id, 'automation', [], $updated->toArray(), $actor?->id, [
                'source' => 'form automation',
                'rule' => 'material added from modal',
            ]);

            return $updated;
        });
    }

    public function createBooking(WorkOrder $workOrder, array $data, ?User $actor = null): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $data, $actor) {
            $old = $workOrder->toArray();

            $this->repository->addBooking($workOrder, [
                ...$data,
                'actor_id' => $actor?->id,
            ]);

            $updated = $this->repository->update($workOrder, [
                'booking_date' => $data['booking_date'],
                'booking_time' => $data['start_time'] ?? null,
                'status' => 'scheduled',
                'updated_by' => $actor?->id,
            ]);

            $this->log('work_orders', $updated->id, 'automation', $old, $updated->toArray(), $actor?->id, [
                'source' => 'form automation',
                'rule' => 'booking created updates status to scheduled',
            ]);

            return $updated;
        });
    }

    public function updateStatus(WorkOrder $workOrder, string $status, ?User $actor = null): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $status, $actor) {
            $old = $workOrder->toArray();
            $updated = $this->repository->update($workOrder, [
                'status' => $status,
                'completed_at' => $status === 'completed' ? now() : null,
                'updated_by' => $actor?->id,
            ]);

            if ($status === 'completed') {
                \App\Models\Task::query()->create([
                    'title' => "Quality Review: {$updated->work_order_number}",
                    'category' => 'operations',
                    'status' => 'pending',
                    'priority' => 'medium',
                    'assigned_to' => $actor?->id ?? User::query()->value('id'),
                    'related_type' => 'work_order',
                    'related_id' => $updated->id,
                    'related_work_order_id' => $updated->id,
                    'created_by' => $actor?->id,
                    'updated_by' => $actor?->id,
                    'notes' => [['note' => 'Automatically created from work order completion.', 'at' => now()->toIso8601String()]],
                ]);
            }

            $this->log('work_orders', $updated->id, 'automation', $old, $updated->toArray(), $actor?->id, [
                'source' => 'form automation',
                'rule' => 'status changed from quick action',
            ]);

            return $updated;
        });
    }

    public function uploadPhoto(WorkOrder $workOrder, array $data, ?User $actor = null): WorkOrder
    {
        return DB::transaction(function () use ($workOrder, $data, $actor) {
            $this->repository->addPhoto($workOrder, [
                'photo_url' => $data['photo_url'],
                'description' => $data['description'] ?? null,
                'actor_id' => $actor?->id,
            ]);

            $updated = $workOrder->refresh();
            $this->log('work_order_photos', $workOrder->id, 'create', [], ['photo_url' => $data['photo_url']], $actor?->id, [
                'source' => 'form automation',
            ]);

            return $updated;
        });
    }

    private function log(string $table, string $recordId, string $action, array $old, array $new, ?string $userId, array $metadata = []): void
    {
        ChangeLog::query()->create([
            'table_name' => $table,
            'record_id' => $recordId,
            'action' => $action,
            'user_id' => $userId,
            'changes' => ['old' => $old, 'new' => $new],
            'metadata' => $metadata,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    private function nextWorkOrderNumber(): string
    {
        $last = WorkOrder::query()->orderByDesc('created_at')->value('work_order_number');
        $number = 1;

        if ($last && preg_match('/WO-(\d+)/', $last, $matches)) {
            $number = ((int) $matches[1]) + 1;
        }

        return sprintf('WO-%04d', $number);
    }
}
