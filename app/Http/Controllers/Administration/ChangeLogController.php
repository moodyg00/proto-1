<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Models\ChangeLog;
use App\Services\Administration\AdministrationService;
use Inertia\Inertia;
use Inertia\Response;

class ChangeLogController extends Controller
{
    public function __construct(private readonly AdministrationService $service) {}

    public function index(): Response
    {
        $data = $this->service->listChangeLogs(
            request()->only(['table_name', 'action', 'user_id', 'date_from', 'date_to'])
        );
        return Inertia::render('Administration/ChangeLog/Index', $data);
    }

    public function show(ChangeLog $changeLog): Response
    {
        $data = $this->service->showChangeLog($changeLog->id);
        return Inertia::render('Administration/ChangeLog/Show', $data);
    }
}
