<?php

namespace App\Filament\Pages;

use App\Models\Contact;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Organization;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Pages\Page;

class CrmDashboard extends Page
{
    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Customer Relations';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.crm-dashboard';

    public string $startDate;

    public string $endDate;

    public array $heroStats = [];

    public array $metricCards = [];

    public array $leadSummary = [];

    public array $topOrganizations = [];

    public array $topPeople = [];

    public array $recentLeads = [];

    public array $stageBreakdown = [];

    public array $sourceRevenue = [];

    public array $statusRevenue = [];

    public function mount(): void
    {
        $this->startDate = (string) request()->query('start_date', now()->subDays(30)->toDateString());
        $this->endDate = (string) request()->query('end_date', now()->toDateString());

        if (! $this->isValidDate($this->startDate)) {
            $this->startDate = now()->subDays(30)->toDateString();
        }

        if (! $this->isValidDate($this->endDate)) {
            $this->endDate = now()->toDateString();
        }

        if ($this->startDate > $this->endDate) {
            [$this->startDate, $this->endDate] = [$this->endDate, $this->startDate];
        }

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

    public function getHeading(): string | Htmlable
    {
        return 'Dashboard';
    }

    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            'uncontacted' => 'New',
            'contacted' => 'Follow Up',
            'quoted' => 'Prospect',
            'booked' => 'Negotiation',
            'converted' => 'Won',
            'lost' => 'Lost',
            default => str($status)->headline()->toString(),
        };
    }

    public function getSourceLabel(?string $source): string
    {
        return Lead::sourceOptions()[$source] ?? 'Unknown';
    }

    protected function hydrateDashboard(): void
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        $leads = Lead::query()
            ->with(['organization'])
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->get();

        $wonLeads = $leads->where('status', 'converted');
        $lostLeads = $leads->where('status', 'lost');
        $openLeads = $leads->whereNotIn('status', ['converted', 'lost']);

        $wonRevenue = (float) $wonLeads->sum(fn (Lead $lead): float => (float) ($lead->expected_value ?? 0));
        $lostRevenue = (float) $lostLeads->sum(fn (Lead $lead): float => (float) ($lead->expected_value ?? 0));
        $totalRevenue = (float) $leads->sum(fn (Lead $lead): float => (float) ($lead->expected_value ?? 0));
        $closedRevenue = max($wonRevenue + $lostRevenue, 1);
        $daySpan = max($start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1, 1);
        $averageLeadValue = (float) $leads->avg(fn (Lead $lead): float => (float) ($lead->expected_value ?? 0));
        $opportunityCount = Opportunity::query()->whereBetween('created_at', [$start, $end])->count();
        $personCount = Contact::query()->whereBetween('created_at', [$start, $end])->count();
        $organizationCount = Organization::query()->whereBetween('created_at', [$start, $end])->count();
        $openTicketCount = Ticket::query()
            ->whereBetween('created_at', [$start, $end])
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        $this->heroStats = [
            [
                'label' => 'Won Revenue',
                'value' => $wonRevenue,
                'share' => $wonRevenue > 0 ? round(($wonRevenue / $closedRevenue) * 100) : 0,
                'tone' => 'emerald',
                'note' => $wonLeads->count() . ' closed-won leads',
            ],
            [
                'label' => 'Lost Revenue',
                'value' => $lostRevenue,
                'share' => $lostRevenue > 0 ? round(($lostRevenue / $closedRevenue) * 100) : 0,
                'tone' => 'rose',
                'note' => $lostLeads->count() . ' closed-lost leads',
            ],
        ];

        $this->metricCards = [
            [
                'label' => 'Average Lead Value',
                'value' => $this->formatMoney($averageLeadValue),
                'meta' => $openLeads->count() . ' open leads in play',
            ],
            [
                'label' => 'Total Leads',
                'value' => $this->formatCount($leads->count()),
                'meta' => $this->formatMoney($totalRevenue) . ' in tracked value',
            ],
            [
                'label' => 'Average Leads Per Day',
                'value' => $this->formatCount(round($leads->count() / $daySpan, 2), 2),
                'meta' => $daySpan . ' day reporting window',
            ],
            [
                'label' => 'Total Quotations',
                'value' => $this->formatCount($opportunityCount),
                'meta' => $openTicketCount . ' open tickets in the same range',
            ],
            [
                'label' => 'Total Persons',
                'value' => $this->formatCount($personCount),
                'meta' => 'Contact records created in range',
            ],
            [
                'label' => 'Total Organizations',
                'value' => $this->formatCount($organizationCount),
                'meta' => 'Organizations created in range',
            ],
        ];

        $this->leadSummary = [
            [
                'label' => 'Total Leads',
                'value' => $leads->count(),
                'width' => $leads->count() > 0 ? 100 : 0,
                'tone' => 'slate',
            ],
            [
                'label' => 'Won Leads',
                'value' => $wonLeads->count(),
                'width' => $leads->count() > 0 ? round(($wonLeads->count() / $leads->count()) * 100) : 0,
                'tone' => 'emerald',
            ],
            [
                'label' => 'Lost Leads',
                'value' => $lostLeads->count(),
                'width' => $leads->count() > 0 ? round(($lostLeads->count() / $leads->count()) * 100) : 0,
                'tone' => 'rose',
            ],
        ];

        $this->topOrganizations = $leads
            ->filter(fn (Lead $lead): bool => filled($lead->organization?->name))
            ->groupBy(fn (Lead $lead): string => (string) $lead->organization?->name)
            ->map(fn ($group, string $name): array => [
                'name' => $name,
                'lead_count' => $group->count(),
                'value' => (float) $group->sum(fn (Lead $lead): float => (float) ($lead->expected_value ?? 0)),
            ])
            ->sortByDesc('value')
            ->take(5)
            ->values()
            ->all();

        $this->topPeople = $leads
            ->sortByDesc(fn (Lead $lead): float => (float) ($lead->expected_value ?? 0))
            ->take(5)
            ->map(fn (Lead $lead): array => [
                'name' => $lead->name,
                'organization' => $lead->organization?->name,
                'value' => (float) ($lead->expected_value ?? 0),
                'initials' => collect(explode(' ', trim((string) $lead->name)))
                    ->filter()
                    ->take(2)
                    ->map(fn (string $segment): string => strtoupper(substr($segment, 0, 1)))
                    ->implode(''),
            ])
            ->values()
            ->all();

        $this->recentLeads = $leads
            ->take(5)
            ->map(fn (Lead $lead): array => [
                'name' => $lead->name,
                'organization' => $lead->organization?->name,
                'source' => $this->getSourceLabel($lead->source),
                'status' => $this->getStatusLabel($lead->status),
                'value' => (float) ($lead->expected_value ?? 0),
            ])
            ->values()
            ->all();

        $this->stageBreakdown = collect(['uncontacted', 'contacted', 'quoted', 'booked'])
            ->map(fn (string $status): array => [
                'label' => $this->getStatusLabel($status),
                'count' => $openLeads->where('status', $status)->count(),
            ])
            ->filter(fn (array $stage): bool => $stage['count'] > 0)
            ->values()
            ->all();

        $this->sourceRevenue = collect(Lead::sourceOptions())
            ->map(function (string $label, string $source) use ($leads, $totalRevenue): array {
                $value = (float) $leads
                    ->where('source', $source)
                    ->sum(fn (Lead $lead): float => (float) ($lead->expected_value ?? 0));

                return [
                    'label' => $label,
                    'value' => $value,
                    'width' => $totalRevenue > 0 ? round(($value / $totalRevenue) * 100) : 0,
                ];
            })
            ->filter(fn (array $source): bool => $source['value'] > 0)
            ->sortByDesc('value')
            ->take(5)
            ->values()
            ->all();

        $this->statusRevenue = collect(['uncontacted', 'contacted', 'quoted', 'booked', 'converted', 'lost'])
            ->map(function (string $status) use ($leads, $totalRevenue): array {
                $value = (float) $leads
                    ->where('status', $status)
                    ->sum(fn (Lead $lead): float => (float) ($lead->expected_value ?? 0));

                return [
                    'label' => $this->getStatusLabel($status),
                    'value' => $value,
                    'width' => $totalRevenue > 0 ? round(($value / $totalRevenue) * 100) : 0,
                ];
            })
            ->filter(fn (array $status): bool => $status['value'] > 0)
            ->values()
            ->all();
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
