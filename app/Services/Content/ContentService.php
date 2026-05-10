<?php

namespace App\Services\Content;

use App\Models\BlogPost;
use App\Models\ChangeLog;
use App\Models\Page;
use App\Models\PhysicalDesign;
use App\Models\PhysicalDesignVersion;
use App\Models\ProductDesign;
use App\Repositories\Content\ContentRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContentService
{
    public function __construct(private readonly ContentRepository $repository)
    {
    }

    public function dashboardPayload(): array
    {
        return [
            'stats' => $this->repository->dashboardStats(),
            'recentBlogPosts' => $this->repository->recentBlogPosts(),
            'physicalDesigns' => $this->repository->activeDesigns(),
            'upcomingPublications' => $this->repository->upcomingPublications(),
            'quickLinks' => [
                ['label' => 'Create Blog Post', 'href' => '/content/blog-posts/create'],
                ['label' => 'Create Page', 'href' => '/content/pages/create'],
                ['label' => 'Create Physical Design', 'href' => '/content/physical-designs/create'],
                ['label' => 'Upload Image', 'href' => '/content/image-files'],
                ['label' => 'Upload Asset', 'href' => '/content/assets'],
                ['label' => 'View All Blog Posts', 'href' => '/content/blog-posts'],
                ['label' => 'View All Designs', 'href' => '/content/physical-designs'],
                ['label' => 'Media Library', 'href' => '/content/image-files'],
            ],
        ];
    }

    public function createBlogPost(array $data, ?string $userId): BlogPost
    {
        $post = BlogPost::query()->create([
            ...$data,
            'slug' => $data['slug'] ?? Str::slug($data['title']).'-'.Str::lower(Str::random(4)),
            'status' => $data['status'] ?? 'draft',
            'author_id' => $data['author_id'] ?? $userId,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        $this->log('blog_posts', $post->id, 'create', [], $post->toArray(), $userId);

        return $post;
    }

    public function updateBlogPost(BlogPost $post, array $data, ?string $userId): BlogPost
    {
        $old = $post->toArray();
        $post->update([
            ...$data,
            'slug' => $data['slug'] ?? $post->slug,
            'updated_by' => $userId,
        ]);

        $this->log('blog_posts', $post->id, 'update', $old, $post->fresh()->toArray(), $userId);

        return $post->fresh();
    }

    public function publishOrScheduleBlogPost(BlogPost $post, array $data, ?string $userId): BlogPost
    {
        $old = $post->toArray();
        $publishAt = $data['published_at'] ?? now()->toDateTimeString();
        $status = now()->gt($publishAt) || now()->equalTo($publishAt) ? 'published' : 'scheduled';

        $post->update([
            'published_at' => $publishAt,
            'status' => $status,
            'updated_by' => $userId,
        ]);

        $this->log('blog_posts', $post->id, 'automation', $old, $post->fresh()->toArray(), $userId, [
            'source' => 'form automation',
            'rule' => 'publish or schedule post',
        ]);

        return $post->fresh();
    }

    public function unpublishBlogPost(BlogPost $post, ?string $userId): BlogPost
    {
        $old = $post->toArray();
        $post->update([
            'status' => 'archived',
            'updated_by' => $userId,
        ]);

        $this->log('blog_posts', $post->id, 'automation', $old, $post->fresh()->toArray(), $userId, [
            'source' => 'form automation',
            'rule' => 'unpublish post to archived',
        ]);

        return $post->fresh();
    }

    public function duplicateBlogPost(BlogPost $post, ?string $userId): BlogPost
    {
        $copy = BlogPost::query()->create([
            ...collect($post->toArray())->except(['id', 'created_at', 'updated_at'])->toArray(),
            'title' => $post->title.' (Copy)',
            'slug' => $post->slug.'-copy-'.Str::lower(Str::random(3)),
            'status' => 'draft',
            'published_at' => null,
            'view_count' => 0,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        $this->log('blog_posts', $copy->id, 'create', [], $copy->toArray(), $userId, ['source' => 'form automation', 'rule' => 'duplicate post']);

        return $copy;
    }

    public function createPage(array $data, ?string $userId): Page
    {
        $page = Page::query()->create([
            ...$data,
            'status' => $data['status'] ?? 'draft',
            'is_published' => $data['is_published'] ?? false,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        $this->log('pages', $page->id, 'create', [], $page->toArray(), $userId);

        return $page;
    }

    public function updatePage(Page $page, array $data, ?string $userId): Page
    {
        $old = $page->toArray();
        $page->update([
            ...$data,
            'updated_by' => $userId,
        ]);
        $this->log('pages', $page->id, 'update', $old, $page->fresh()->toArray(), $userId);

        return $page->fresh();
    }

    public function publishPage(Page $page, bool $publish, ?string $userId): Page
    {
        $old = $page->toArray();
        $page->update([
            'is_published' => $publish,
            'status' => $publish ? 'published' : 'archived',
            'updated_by' => $userId,
        ]);
        $this->log('pages', $page->id, 'automation', $old, $page->fresh()->toArray(), $userId, ['source' => 'form automation', 'rule' => $publish ? 'publish page' : 'unpublish page']);

        return $page->fresh();
    }

    public function duplicatePage(Page $page, ?string $userId): Page
    {
        $copy = Page::query()->create([
            ...collect($page->toArray())->except(['id', 'created_at', 'updated_at'])->toArray(),
            'page_title' => $page->page_title.' (Copy)',
            'page_slug' => $page->page_slug.'-copy-'.Str::lower(Str::random(3)),
            'status' => 'draft',
            'is_published' => false,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        $this->log('pages', $copy->id, 'create', [], $copy->toArray(), $userId, ['source' => 'form automation', 'rule' => 'duplicate page']);

        return $copy;
    }

    public function createPhysicalDesign(array $data, ?string $userId): PhysicalDesign
    {
        return DB::transaction(function () use ($data, $userId) {
            $design = PhysicalDesign::query()->create([
                ...$data,
                'status' => $data['status'] ?? 'draft',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $version = PhysicalDesignVersion::query()->create([
                'physical_design_id' => $design->id,
                'version_number' => '1.0',
                'files' => $data['files'] ?? [],
                'notes' => 'Initial version',
                'status' => $design->status,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $design->update(['latest_version_id' => $version->id]);
            $this->log('physical_designs', $design->id, 'create', [], $design->fresh()->toArray(), $userId);

            return $design->fresh();
        });
    }

    public function updatePhysicalDesign(PhysicalDesign $design, array $data, ?string $userId): PhysicalDesign
    {
        $old = $design->toArray();
        $design->update([
            ...$data,
            'updated_by' => $userId,
        ]);
        $this->log('physical_designs', $design->id, 'update', $old, $design->fresh()->toArray(), $userId);

        return $design->fresh();
    }

    public function createDesignVersion(PhysicalDesign $design, array $data, ?string $userId): PhysicalDesignVersion
    {
        $latestVersion = PhysicalDesignVersion::query()->where('physical_design_id', $design->id)->orderByDesc('created_at')->value('version_number');
        $nextVersion = $latestVersion ? (string) (round((float) $latestVersion + 0.1, 1)) : '1.0';

        $version = PhysicalDesignVersion::query()->create([
            'physical_design_id' => $design->id,
            'version_number' => $data['version_number'] ?? $nextVersion,
            'files' => $data['files'] ?? [],
            'notes' => $data['notes'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        $design->update(['latest_version_id' => $version->id, 'updated_by' => $userId]);

        $this->log('physical_design_versions', $version->id, 'automation', [], $version->toArray(), $userId, [
            'source' => 'form automation',
            'rule' => 'new version set as latest',
            'physical_design_id' => $design->id,
        ]);

        return $version;
    }

    public function approveDesign(PhysicalDesign $design, ?string $userId): PhysicalDesign
    {
        $old = $design->toArray();
        $design->update(['status' => 'approved', 'updated_by' => $userId]);
        PhysicalDesignVersion::query()->where('physical_design_id', $design->id)->update(['status' => 'approved', 'updated_by' => $userId]);

        $this->log('physical_designs', $design->id, 'automation', $old, $design->fresh()->toArray(), $userId, [
            'source' => 'form automation',
            'rule' => 'approve design and all versions',
        ]);

        return $design->fresh();
    }

    public function archiveDesign(PhysicalDesign $design, ?string $userId): PhysicalDesign
    {
        $old = $design->toArray();
        $design->update(['status' => 'archived', 'updated_by' => $userId]);

        $this->log('physical_designs', $design->id, 'automation', $old, $design->fresh()->toArray(), $userId, [
            'source' => 'form automation',
            'rule' => 'archive design',
        ]);

        return $design->fresh();
    }

    public function linkDesignToProduct(PhysicalDesign $design, array $data, ?string $userId): ProductDesign
    {
        $link = ProductDesign::query()->create([
            'product_id' => $data['product_id'],
            'physical_design_id' => $design->id,
            'is_default' => $data['is_default'] ?? false,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        $this->log('product_designs', $link->id, 'automation', [], $link->toArray(), $userId, [
            'source' => 'form automation',
            'rule' => 'link design to product',
        ]);

        return $link;
    }

    private function log(string $table, string $recordId, string $action, array $old, array $new, ?string $userId, array $metadata = []): void
    {
        ChangeLog::query()->create([
            'table_name' => $table,
            'record_id' => $recordId,
            'action' => $action,
            'user_id' => $userId,
            'changes' => ['old' => $old, 'new' => $new],
            'metadata' => $metadata,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }
}
