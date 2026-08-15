<?php

namespace App\Services\Shop;

use App\Models\Content\Post;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SitemapService
{
    public const CACHE_KEY = 'sitemap.urls.v3';

    public const CACHE_TTL_HOURS = 12;

    /**
     * @return list<array{loc: string, lastmod: \Illuminate\Support\Carbon|\Carbon\CarbonInterface, changefreq: string, priority: string, images?: list<string>}>
     */
    public function urls(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addHours(self::CACHE_TTL_HOURS), fn () => $this->build());
    }

    /**
     * @return list<array{loc: string, lastmod: \Illuminate\Support\Carbon|\Carbon\CarbonInterface, changefreq: string, priority: string, images?: list<string>}>
     */
    public function refresh(): array
    {
        $urls = $this->build();

        Cache::put(self::CACHE_KEY, $urls, now()->addHours(self::CACHE_TTL_HOURS));

        return $urls;
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return list<array{loc: string, lastmod: \Illuminate\Support\Carbon|\Carbon\CarbonInterface, changefreq: string, priority: string, images?: list<string>}>
     */
    protected function build(): array
    {
        $categories = Category::query()
            ->select(['id', 'slug', 'slug_fa', 'updated_at'])
            ->orderBy('id')
            ->get();

        $products = Product::query()
            ->select(['id', 'slug', 'slug_fa', 'file_path', 'updated_at'])
            ->orderBy('id')
            ->get();

        $posts = Post::query()
            ->published()
            ->select(['id', 'slug', 'updated_at', 'published_at', 'status'])
            ->orderBy('id')
            ->get();

        $latestUpdate = collect([
            $categories->max('updated_at'),
            $products->max('updated_at'),
            $posts->max('updated_at'),
        ])
            ->filter()
            ->max();

        $settingsUpdatedAt = null;
        try {
            $raw = DB::table('settings')->max('updated_at');
            $settingsUpdatedAt = $raw ? \Illuminate\Support\Carbon::parse($raw) : null;
        } catch (\Throwable) {
            // settings table may not exist in some environments
        }

        $urls = [
            [
                'loc' => route('home'),
                'lastmod' => $latestUpdate ?? now(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => route('contact.index'),
                'lastmod' => $settingsUpdatedAt ?? $latestUpdate ?? now(),
                'changefreq' => 'yearly',
                'priority' => '0.5',
            ],
            [
                'loc' => route('post.index'),
                'lastmod' => $posts->max('updated_at') ?? $latestUpdate ?? now(),
                'changefreq' => 'daily',
                'priority' => '0.7',
            ],
        ];

        foreach ($categories as $category) {
            $urls[] = [
                'loc' => $category->url,
                'lastmod' => $category->updated_at,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        foreach ($products as $product) {
            $entry = [
                'loc' => $product->url,
                'lastmod' => $product->updated_at,
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ];

            if (filled($product->file_path)) {
                $entry['images'] = [url(Storage::url($product->file_path))];
            }

            $urls[] = $entry;
        }

        foreach ($posts as $post) {
            $urls[] = [
                'loc' => route('post.view', ['slug' => $post->slug]),
                'lastmod' => $post->updated_at,
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ];
        }

        return $urls;
    }
}
