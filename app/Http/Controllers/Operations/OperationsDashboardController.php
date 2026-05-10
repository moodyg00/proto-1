<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Services\Operations\WorkOrderService;
use Inertia\Inertia;
use Inertia\Response;

class OperationsDashboardController extends Controller
{
    public function __invoke(WorkOrderService $service): Response
    {
        // Implements Operations Dashboard from operations-views-and-actions.md
        return Inertia::render('Operations/Dashboard', $service->dashboardPayload());
    }
}
