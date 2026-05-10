<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\StoreServiceRequest;
use App\Http\Requests\Administration\UpdateServiceRequest;
use App\Models\Service;
use App\Services\Administration\AdministrationService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function __construct(private readonly AdministrationService $service) {}

    public function index(): Response
    {
        $data = $this->service->listServices(request()->only(['search', 'category', 'is_active']));
        return Inertia::render('Administration/Services/Index', $data);
    }

    public function create(): Response
    {
        return Inertia::render('Administration/Services/Create');
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $svc = $this->service->createService($request->validated());
        return redirect()->route('administration.services.show', $svc)->with('success', 'Service created.');
    }

    public function show(Service $service): Response
    {
        $data = $this->service->showService($service->id);
        return Inertia::render('Administration/Services/Show', $data);
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $this->service->updateService($service, $request->validated());
        return back()->with('success', 'Service updated.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->service->deleteService($service);
        return redirect()->route('administration.services.index')->with('success', 'Service deleted.');
    }
}
