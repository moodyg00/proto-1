<?php

namespace App\Services\CRM;

use App\Models\ChangeLog;
use App\Models\Lead;
use App\Models\User;
use App\Repositories\CRM\LeadRepository;
use Illuminate\Support\Facades\DB;

class LeadService
{
    public function __construct(private readonly LeadRepository $repository)
    {
    }

    public function dashboardPayload(): array
    {
        return [
            'stats' => $this->repository->dashboardStats(),
            'leads' => $this->repository->recent(10),
            'opportunities' => \App\Models\Opportunity::query()->latest()->limit(10)->get(),
            'tickets' => \App\Models\Ticket::query()->latest()->limit(10)->get(),
            'quickLinks' => [
                ['label' => 'Create New Lead', 'href' => '/crm/leads/create'],
                ['label' => 'Create New Opportunity', 'href' => '/crm/opportunities/create'],
                ['label' => 'Create New Ticket', 'href' => '/crm/tickets/create'],
                ['label' => 'View All Contacts', 'href' => '/crm/contacts'],
                ['label' => 'View All Estimates', 'href' => '/crm/estimates'],
                ['label' => 'View All Tickets', 'href' => '/crm/tickets'],
            ],
        ];
    }

    public function indexPayload(array $filters): array
    {
        return [
            'leads' => $this->repository->paginated($filters),
            'filters' => $filters,
            'users' => User::query()->orderBy('full_name')->get(['id', 'full_name']),
        ];
    }

    public function create( array $data, ?User $actor = null): Lead
    {
        return DB::transaction(function () use ($data, $actor) {
            $lead = $this->repository->create([
                ...$data,
                'status' => $data['status'] ?? 'uncontacted',
                'created_by' => $actor?->id,
                'updated_by' => $actor?->id,
            ]);

            $this->log($lead->id, 'create', [], $lead->toArray(), $actor?->id);

            return $lead;
        });
    }

    public function update(Lead $lead, array $data, ?User $actor = null): Lead
    {
        return DB::transaction(function () use ($lead, $data, $actor) {
            $old = $lead->toArray();
            $updated = $this->repository->update($lead, [
                ...$data,
                'updated_by' => $actor?->id,
            ]);

            $this->log($updated->id, 'update', $old, $updated->toArray(), $actor?->id);

            return $updated;
        });
    }

    public function delete(Lead $lead, ?User $actor = null): void
    {
        DB::transaction(function () use ($lead, $actor) {
            $old = $lead->toArray();
            $this->repository->delete($lead);
            $this->log($lead->id, 'delete', $old, [], $actor?->id);
        });
    }

    private function log(string $recordId, string $action, array $old, array $new, ?string $userId): void
    {
        ChangeLog::query()->create([
            'table_name' => 'leads',
            'record_id' => $recordId,
            'action' => $action,
            'user_id' => $userId,
            'changes' => ['old' => $old, 'new' => $new],
            'metadata' => ['source' => 'form automation'],
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }
}
