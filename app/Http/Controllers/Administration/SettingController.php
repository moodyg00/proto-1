<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\StoreSettingRequest;
use App\Models\Setting;
use App\Services\Administration\AdministrationService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function __construct(private readonly AdministrationService $service) {}

    public function index(): Response
    {
        $data = $this->service->listSettings(request()->only(['module']));
        return Inertia::render('Administration/Settings/Index', $data);
    }

    public function store(StoreSettingRequest $request): RedirectResponse
    {
        $this->service->createSetting($request->validated());
        return back()->with('success', 'Setting created.');
    }

    public function update(StoreSettingRequest $request, Setting $setting): RedirectResponse
    {
        $this->service->updateSetting($setting, $request->validated());
        return back()->with('success', 'Setting updated.');
    }
}
