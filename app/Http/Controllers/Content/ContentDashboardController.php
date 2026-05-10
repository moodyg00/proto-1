<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Services\Content\ContentService;
use Inertia\Inertia;
use Inertia\Response;

class ContentDashboardController extends Controller
{
    public function __invoke(ContentService $service): Response
    {
        return Inertia::render('Content/Dashboard', $service->dashboardPayload());
    }
}
