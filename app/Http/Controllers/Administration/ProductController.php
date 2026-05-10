<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\StoreProductRequest;
use App\Http\Requests\Administration\UpdateProductRequest;
use App\Models\Product;
use App\Services\Administration\AdministrationService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(private readonly AdministrationService $service) {}

    public function index(): Response
    {
        $data = $this->service->listProducts(request()->only(['search', 'category']));
        return Inertia::render('Administration/Products/Index', $data);
    }

    public function create(): Response
    {
        return Inertia::render('Administration/Products/Create');
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = $this->service->createProduct($request->validated());
        return redirect()->route('administration.products.show', $product)->with('success', 'Product created.');
    }

    public function show(Product $product): Response
    {
        $data = $this->service->showProduct($product->id);
        return Inertia::render('Administration/Products/Show', $data);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->service->updateProduct($product, $request->validated());
        return back()->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->service->deleteProduct($product);
        return redirect()->route('administration.products.index')->with('success', 'Product deleted.');
    }
}
