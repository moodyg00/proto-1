<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Services\Accounting\AccountingService;
use Inertia\Inertia;
use Inertia\Response;

class AccountingDashboardController extends Controller
{
    public function __invoke(AccountingService $service): Response
    {
        // Implements Accounting Dashboard from accounting-views-and-actions.md
        return Inertia::render('Accounting/Dashboard', $service->dashboardPayload());
    }
}
