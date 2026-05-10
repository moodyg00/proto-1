<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\AdjustStockRequest;
use App\Models\Product;
use App\Services\Administration\AdministrationService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function __construct(private readonly AdministrationService $service) {}

    public function index(): Response
    {
        $data = $this->service->listInventory(request()->only(['search', 'low_stock']));
        return Inertia::render('Administration/Inventory/Index', $data);
    }

    public function adjust(AdjustStockRequest $request, Product $product): RedirectResponse
    {
        $this->service->adjustStock($product, $request->validated());
        return back()->with('success', 'Stock adjusted.');
    }
}
