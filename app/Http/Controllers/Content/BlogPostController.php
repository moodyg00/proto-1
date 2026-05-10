<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\Content\PublishBlogPostRequest;
use App\Http\Requests\Content\StoreBlogPostRequest;
use App\Http\Requests\Content\UpdateBlogPostRequest;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Repositories\Content\ContentRepository;
use App\Services\Content\ContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogPostController extends Controller
{
    public function __construct(
        private readonly ContentRepository $repository,
        private readonly ContentService $service
    ) {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Content/BlogPosts/Index', [
            'posts' => $this->repository->blogPostsPaginated($request->only(['status'])),
            'filters' => $request->only(['status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Content/BlogPosts/Create', [
            'categories' => BlogCategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreBlogPostRequest $request): RedirectResponse
    {
        $post = $this->service->createBlogPost($request->validated(), $request->user()?->id);

        return redirect()->route('content.blog-posts.show', $post)->with('success', 'Blog post created.');
    }

    public function show(BlogPost $blogPost): Response
    {
        return Inertia::render('Content/BlogPosts/Show', [
            'post' => $blogPost,
            'categories' => BlogCategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateBlogPostRequest $request, BlogPost $blogPost): RedirectResponse
    {
        $this->service->updateBlogPost($blogPost, $request->validated(), $request->user()?->id);

        return back()->with('success', 'Blog post updated.');
    }

    public function publish(PublishBlogPostRequest $request, BlogPost $blogPost): RedirectResponse
    {
        $this->service->publishOrScheduleBlogPost($blogPost, $request->validated(), $request->user()?->id);

        return back()->with('success', 'Publication status updated.');
    }

    public function unpublish(Request $request, BlogPost $blogPost): RedirectResponse
    {
        $this->service->unpublishBlogPost($blogPost, $request->user()?->id);

        return back()->with('success', 'Post moved to archived.');
    }

    public function duplicate(Request $request, BlogPost $blogPost): RedirectResponse
    {
        $copy = $this->service->duplicateBlogPost($blogPost, $request->user()?->id);

        return redirect()->route('content.blog-posts.show', $copy)->with('success', 'Post duplicated.');
    }

    public function destroy(BlogPost $blogPost): RedirectResponse
    {
        $blogPost->delete();

        return redirect()->route('content.blog-posts.index')->with('success', 'Blog post deleted.');
    }
}
