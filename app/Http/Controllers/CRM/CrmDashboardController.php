<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Services\CRM\LeadService;
use Inertia\Inertia;
use Inertia\Response;

class CrmDashboardController extends Controller
{
    public function __invoke(LeadService $service): Response
    {
        // Implements CRM Dashboard from crm-views-and-actions.md
        return Inertia::render('CRM/Dashboard', $service->dashboardPayload());
    }
}
