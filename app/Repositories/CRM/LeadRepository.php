<?php

namespace App\Repositories\CRM;

use App\Models\Lead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LeadRepository
{
    public function dashboardStats(): array
    {
        return [
            'newLeadsToday' => Lead::query()->whereDate('created_at', now()->toDateString())->count(),
            'uncontactedLeads' => Lead::query()->where('status', 'uncontacted')->count(),
            'pipelineValue' => \App\Models\Opportunity::query()->whereNotIn('status', ['won', 'lost'])->sum('estimated_value'),
            'openTickets' => \App\Models\Ticket::query()->whereNotIn('status', ['resolved', 'closed'])->count(),
            'ticketsRequiringResponse' => \App\Models\Ticket::query()->where('status', 'open')->count(),
            'convertedThisWeek' => Lead::query()->whereNotNull('converted_at')->whereBetween('converted_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];
    }

    public function paginated(array $filters): LengthAwarePaginator
    {
        return Lead::query()
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['source'] ?? null, fn ($q, $source) => $q->where('source', $source))
            ->when($filters['assigned_to'] ?? null, fn ($q, $assigned) => $q->where('assigned_to', $assigned))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();
    }

    public function recent(int $limit = 10): Collection
    {
        return Lead::query()->latest()->limit($limit)->get();
    }

    public function create(array $payload): Lead
    {
        return Lead::query()->create($payload);
    }

    public function update(Lead $lead, array $payload): Lead
    {
        $lead->update($payload);

        return $lead->refresh();
    }

    public function delete(Lead $lead): void
    {
        $lead->delete();
    }
}
