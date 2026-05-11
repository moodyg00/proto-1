<?php

namespace App\Services\Operations;

use App\Repositories\Operations\WorkOrderRepository;

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
                ['label' => 'Purchase Materials', 'href' => '/admin/jobs?view_type=table&tableFilters[status][value]=in_progress'],
                ['label' => 'Refunds & Credits', 'href' => '/accounting/credits'],
                ['label' => 'Manage Contractors', 'href' => '/crm/contacts?type=contractor'],
                ['label' => 'Manage Customers', 'href' => '/crm/contacts?type=customer'],
                ['label' => 'Services Catalog', 'href' => '/administration/services'],
                ['label' => 'View All Invoices', 'href' => '/accounting/invoices'],
            ],
        ];
    }
}
