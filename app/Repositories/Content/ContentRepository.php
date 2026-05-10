<?php

namespace App\Repositories\Content;

use App\Models\Asset;
use App\Models\BlogPost;
use App\Models\ImageFile;
use App\Models\Page;
use App\Models\PhysicalDesign;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ContentRepository
{
    public function dashboardStats(): array
    {
        $mostViewed = BlogPost::query()
            ->whereMonth('published_at', now()->month)
            ->orderByDesc('view_count')
            ->first(['id', 'title', 'view_count']);

        return [
            'publishedBlogPostsThisMonth' => BlogPost::query()->where('status', 'published')->whereMonth('published_at', now()->month)->count(),
            'draftPosts' => BlogPost::query()->where('status', 'draft')->count(),
            'scheduledPublications' => BlogPost::query()->where('status', 'scheduled')->count() + Page::query()->where('status', 'draft')->where('is_published', false)->count(),
            'activePhysicalDesigns' => PhysicalDesign::query()->whereIn('status', ['draft', 'approved'])->count(),
            'designsAwaitingApproval' => PhysicalDesign::query()->where('status', 'draft')->count(),
            'totalMediaFiles' => ImageFile::query()->count() + Asset::query()->count(),
            'mostViewedPostThisMonth' => $mostViewed ? ($mostViewed->title.' ('.$mostViewed->view_count.')') : 'N/A',
        ];
    }

    public function recentBlogPosts(int $limit = 10): Collection
    {
        return BlogPost::query()
            ->leftJoin('users', 'users.id', '=', 'blog_posts.author_id')
            ->orderByDesc('blog_posts.created_at')
            ->limit($limit)
            ->get([
                'blog_posts.*',
                'users.full_name as author_name',
            ]);
    }

    public function activeDesigns(int $limit = 10): Collection
    {
        return PhysicalDesign::query()->whereIn('status', ['draft', 'approved'])->orderByDesc('updated_at')->limit($limit)->get();
    }

    public function upcomingPublications(int $limit = 10): Collection
    {
        return BlogPost::query()->where('status', 'scheduled')->whereNotNull('published_at')->orderBy('published_at')->limit($limit)->get(['id', 'title', 'slug', 'published_at']);
    }

    public function blogPostsPaginated(array $filters): LengthAwarePaginator
    {
        return BlogPost::query()
            ->leftJoin('users', 'users.id', '=', 'blog_posts.author_id')
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('blog_posts.status', $status))
            ->select(['blog_posts.*', 'users.full_name as author_name'])
            ->orderByDesc('blog_posts.updated_at')
            ->paginate(20)
            ->withQueryString();
    }

    public function pagesPaginated(array $filters): LengthAwarePaginator
    {
        return Page::query()
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();
    }

    public function physicalDesignsPaginated(array $filters): LengthAwarePaginator
    {
        return PhysicalDesign::query()
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();
    }

    public function imageFilesPaginated(): LengthAwarePaginator
    {
        return ImageFile::query()->orderByDesc('created_at')->paginate(30);
    }

    public function assetsPaginated(): LengthAwarePaginator
    {
        return Asset::query()->orderByDesc('created_at')->paginate(30);
    }
}
