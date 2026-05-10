<?php

namespace App\Http\Controllers\Banking;

use App\Http\Controllers\Controller;
use App\Services\Banking\BankingService;
use Inertia\Inertia;
use Inertia\Response;

class BankingDashboardController extends Controller
{
    public function __invoke(BankingService $service): Response
    {
        return Inertia::render('Banking/Dashboard', $service->dashboardPayload());
    }
}
