<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\Content\StorePageRequest;
use App\Http\Requests\Content\UpdatePageRequest;
use App\Models\Page;
use App\Repositories\Content\ContentRepository;
use App\Services\Content\ContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function __construct(
        private readonly ContentRepository $repository,
        private readonly ContentService $service
    ) {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Content/Pages/Index', [
            'pages' => $this->repository->pagesPaginated($request->only(['status'])),
            'filters' => $request->only(['status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Content/Pages/Create');
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $page = $this->service->createPage($request->validated(), $request->user()?->id);

        return redirect()->route('content.pages.show', $page)->with('success', 'Page created.');
    }

    public function show(Page $page): Response
    {
        return Inertia::render('Content/Pages/Show', [
            'page' => $page,
        ]);
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $this->service->updatePage($page, $request->validated(), $request->user()?->id);

        return back()->with('success', 'Page updated.');
    }

    public function publish(Request $request, Page $page): RedirectResponse
    {
        $this->service->publishPage($page, true, $request->user()?->id);

        return back()->with('success', 'Page published.');
    }

    public function unpublish(Request $request, Page $page): RedirectResponse
    {
        $this->service->publishPage($page, false, $request->user()?->id);

        return back()->with('success', 'Page unpublished.');
    }

    public function duplicate(Request $request, Page $page): RedirectResponse
    {
        $copy = $this->service->duplicatePage($page, $request->user()?->id);

        return redirect()->route('content.pages.show', $copy)->with('success', 'Page duplicated.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('content.pages.index')->with('success', 'Page deleted.');
    }
}
