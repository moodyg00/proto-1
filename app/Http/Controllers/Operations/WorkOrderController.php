<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Operations\AddMaterialRequest;
use App\Http\Requests\Operations\AssignContractorRequest;
use App\Http\Requests\Operations\CreateBookingRequest;
use App\Http\Requests\Operations\StoreWorkOrderRequest;
use App\Http\Requests\Operations\UpdateWorkOrderRequest;
use App\Http\Requests\Operations\UpdateWorkOrderStatusRequest;
use App\Http\Requests\Operations\UploadWorkOrderPhotoRequest;
use App\Models\WorkOrder;
use App\Services\Operations\WorkOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkOrderController extends Controller
{
    public function __construct(private readonly WorkOrderService $service)
    {
    }

    public function index(Request $request): Response
    {
        // Implements Work Orders Index from operations-views-and-actions.md
        return Inertia::render('Operations/WorkOrders/Index', $this->service->indexPayload($request->only(['status', 'assigned_contractor', 'due_date'])));
    }

    public function create(): Response
    {
        // Implements Work Order Create from operations-views-and-actions.md
        return Inertia::render('Operations/WorkOrders/Create', $this->service->createFormPayload());
    }

    public function store(StoreWorkOrderRequest $request): RedirectResponse
    {
        $workOrder = $this->service->create($request->validated(), $request->user());

        return redirect()->route('operations.work-orders.show', $workOrder)->with('success', 'Work order created.');
    }

    public function show(WorkOrder $workOrder): Response
    {
        // Implements Work Order Show from operations-views-and-actions.md
        return Inertia::render('Operations/WorkOrders/Show', $this->service->showPayload($workOrder));
    }

    public function edit(WorkOrder $workOrder): Response
    {
        // Implements Work Order Edit from operations-views-and-actions.md
        return Inertia::render('Operations/WorkOrders/Edit', [
            ...$this->service->createFormPayload(),
            ...$this->service->showPayload($workOrder),
        ]);
    }

    public function update(UpdateWorkOrderRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $updated = $this->service->update($workOrder, $request->validated(), $request->user());

        return redirect()->route('operations.work-orders.show', $updated)->with('success', 'Work order updated.');
    }

    public function destroy(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $this->service->delete($workOrder, $request->user());

        return redirect()->route('operations.work-orders.index')->with('success', 'Work order deleted.');
    }

    public function assignContractor(AssignContractorRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $this->service->assignContractor($workOrder, $request->validated(), $request->user());

        return back()->with('success', 'Contractor assigned.');
    }

    public function addMaterial(AddMaterialRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $this->service->addMaterial($workOrder, $request->validated(), $request->user());

        return back()->with('success', 'Material added.');
    }

    public function createBooking(CreateBookingRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $this->service->createBooking($workOrder, $request->validated(), $request->user());

        return back()->with('success', 'Booking created and status updated to scheduled.');
    }

    public function updateStatus(UpdateWorkOrderStatusRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $this->service->updateStatus($workOrder, $request->string('status')->toString(), $request->user());

        return back()->with('success', 'Work order status updated.');
    }

    public function uploadPhoto(UploadWorkOrderPhotoRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $this->service->uploadPhoto($workOrder, $request->validated(), $request->user());

        return back()->with('success', 'Photo linked to work order.');
    }
}
