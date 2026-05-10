<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreLeadRequest;
use App\Http\Requests\CRM\UpdateLeadRequest;
use App\Models\Lead;
use App\Services\CRM\LeadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function __construct(private readonly LeadService $service)
    {
    }

    public function index(Request $request): Response
    {
        // Implements Leads Index from crm-views-and-actions.md
        return Inertia::render('CRM/Leads/Index', $this->service->indexPayload($request->only(['status', 'source', 'assigned_to'])));
    }

    public function create(): Response
    {
        return Inertia::render('CRM/Leads/Create', [
            'users' => \App\Models\User::query()->orderBy('full_name')->get(['id', 'full_name']),
        ]);
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $lead = $this->service->create($request->validated(), $request->user());

        return redirect()->route('crm.leads.show', $lead)->with('success', 'Lead created.');
    }

    public function show(Lead $lead): Response
    {
        // Implements Lead Show from crm-views-and-actions.md
        return Inertia::render('CRM/Leads/Show', [
            'lead' => $lead,
            'relatedEstimates' => \App\Models\Estimate::query()->where('lead_id', $lead->id)->get(),
            'relatedOpportunities' => \App\Models\Opportunity::query()->where('contact_id', $lead->contact_id)->get(),
        ]);
    }

    public function edit(Lead $lead): Response
    {
        return Inertia::render('CRM/Leads/Edit', [
            'lead' => $lead,
            'users' => \App\Models\User::query()->orderBy('full_name')->get(['id', 'full_name']),
        ]);
    }

    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        $updated = $this->service->update($lead, $request->validated(), $request->user());

        return redirect()->route('crm.leads.show', $updated)->with('success', 'Lead updated.');
    }

    public function destroy(Request $request, Lead $lead): RedirectResponse
    {
        $this->service->delete($lead, $request->user());

        return redirect()->route('crm.leads.index')->with('success', 'Lead deleted.');
    }
}
