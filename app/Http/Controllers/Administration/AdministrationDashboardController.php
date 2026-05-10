<?php

namespace App\Http\Controllers\Administration;

use App\Services\Administration\AdministrationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdministrationDashboardController
{
    public function __construct(private readonly AdministrationService $service) {}

    public function __invoke(Request $request): Response
    {
        $data = $this->service->getDashboardData();

        return Inertia::render('Administration/Dashboard', [
            'metrics'           => $data['metrics'],
            'recentChangeLog'   => $data['recent_change_log'],
        ]);
    }
}
