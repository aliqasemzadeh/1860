<?php

namespace App\Livewire\Forms\Panel\Content;

use App\Models\Content\Post;
use App\Services\Shop\SitemapService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Form;

class PostForm extends Form
{
    public ?int $postId = null;

    public string $title = '';

    public string $slug = '';

    public ?string $summary = null;

    public ?string $content = null;

    public string $status = 'draft';

    public ?string $featured_image = null;

    public array $product_ids = [];

    public array $tags_array = [];

    public function setPost(Post $post): void
    {
        $this->postId = $post->id;
        $this->title = (string) $post->title;
        $this->slug = (string) $post->slug;
        $this->summary = $post->summary;
        $this->content = $post->content;
        $this->status = (string) $post->status;
        $this->featured_image = $post->featured_image;
        $this->product_ids = $post->products->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->tags_array = $post->tags->pluck('name')->map(fn ($name) => (string) $name)->all();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('posts', 'slug')->ignore($this->postId),
            ],
            'summary' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:draft,published,archived'],
            'featured_image' => ['nullable', 'string', 'max:255'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['numeric', 'exists:products,id'],
            'tags_array' => ['nullable', 'array'],
            'tags_array.*' => ['string', 'max:255'],
        ];
    }

    public function store(): Post
    {
        $validated = $this->validate();

        return DB::transaction(function () use ($validated) {
            $post = Post::query()->create($this->payload($validated));

            $productIds = $this->normalizedProductIds($validated);
            $post->products()->sync($productIds);
            $post->syncTags($validated['tags_array'] ?? []);

            $this->forgetCaches($productIds);

            return $post;
        });
    }

    public function update(Post $post): void
    {
        $validated = $this->validate();

        DB::transaction(function () use ($post, $validated) {
            $previousProductIds = $post->products()->pluck('products.id')->map(fn ($id) => (int) $id)->all();
            $productIds = $this->normalizedProductIds($validated);

            $post->update($this->payload($validated, $post));

            $post->products()->sync($productIds);
            $post->syncTags($validated['tags_array'] ?? []);

            $this->forgetCaches(array_unique([...$previousProductIds, ...$productIds]));
        });
    }

    protected function payload(array $validated, ?Post $existing = null): array
    {
        $publishedAt = $existing?->published_at;

        if ($validated['status'] === 'published') {
            $publishedAt ??= now();
        } elseif ($validated['status'] === 'draft') {
            $publishedAt = null;
        }

        return [
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'summary' => $validated['summary'] ?? null,
            'content' => $validated['content'],
            'featured_image' => $validated['featured_image'] ?? null,
            'status' => $validated['status'],
            'published_at' => $publishedAt,
        ];
    }

    protected function normalizedProductIds(array $validated): array
    {
        return collect($validated['product_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<int>  $productIds
     */
    protected function forgetCaches(array $productIds): void
    {
        foreach ($productIds as $productId) {
            Cache::forget("product.{$productId}.related_posts");
        }

        app(SitemapService::class)->forget();
    }
}
