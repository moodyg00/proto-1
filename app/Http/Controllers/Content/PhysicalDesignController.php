<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\Content\CreatePhysicalDesignVersionRequest;
use App\Http\Requests\Content\LinkPhysicalDesignProductRequest;
use App\Http\Requests\Content\StorePhysicalDesignRequest;
use App\Http\Requests\Content\UpdatePhysicalDesignRequest;
use App\Models\PhysicalDesign;
use App\Models\PhysicalDesignVersion;
use App\Models\Product;
use App\Models\ProductDesign;
use App\Repositories\Content\ContentRepository;
use App\Services\Content\ContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PhysicalDesignController extends Controller
{
    public function __construct(
        private readonly ContentRepository $repository,
        private readonly ContentService $service
    ) {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Content/PhysicalDesigns/Index', [
            'designs' => $this->repository->physicalDesignsPaginated($request->only(['status'])),
            'filters' => $request->only(['status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Content/PhysicalDesigns/Create');
    }

    public function store(StorePhysicalDesignRequest $request): RedirectResponse
    {
        $design = $this->service->createPhysicalDesign($request->validated(), $request->user()?->id);

        return redirect()->route('content.physical-designs.show', $design)->with('success', 'Physical design created.');
    }

    public function show(PhysicalDesign $physicalDesign): Response
    {
        return Inertia::render('Content/PhysicalDesigns/Show', [
            'design' => $physicalDesign,
            'versions' => PhysicalDesignVersion::query()->where('physical_design_id', $physicalDesign->id)->orderByDesc('created_at')->get(),
            'linkedProducts' => ProductDesign::query()->where('physical_design_id', $physicalDesign->id)->leftJoin('products', 'products.id', '=', 'product_designs.product_id')->get(['product_designs.*', 'products.name as product_name']),
            'products' => Product::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdatePhysicalDesignRequest $request, PhysicalDesign $physicalDesign): RedirectResponse
    {
        $this->service->updatePhysicalDesign($physicalDesign, $request->validated(), $request->user()?->id);

        return back()->with('success', 'Design updated.');
    }

    public function createVersion(CreatePhysicalDesignVersionRequest $request, PhysicalDesign $physicalDesign): RedirectResponse
    {
        $this->service->createDesignVersion($physicalDesign, $request->validated(), $request->user()?->id);

        return back()->with('success', 'Design version created and set as latest.');
    }

    public function linkProduct(LinkPhysicalDesignProductRequest $request, PhysicalDesign $physicalDesign): RedirectResponse
    {
        $this->service->linkDesignToProduct($physicalDesign, $request->validated(), $request->user()?->id);

        return back()->with('success', 'Design linked to product.');
    }

    public function approve(Request $request, PhysicalDesign $physicalDesign): RedirectResponse
    {
        $this->service->approveDesign($physicalDesign, $request->user()?->id);

        return back()->with('success', 'Design approved.');
    }

    public function archive(Request $request, PhysicalDesign $physicalDesign): RedirectResponse
    {
        $this->service->archiveDesign($physicalDesign, $request->user()?->id);

        return back()->with('success', 'Design archived.');
    }
}
