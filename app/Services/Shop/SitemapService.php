<?php

namespace App\Services\Shop;

use App\Models\Shop\Category;
use App\Models\Shop\Product;
use Illuminate\Support\Facades\Cache;

class SitemapService
{
    public const CACHE_KEY = 'sitemap.urls.v1';

    public const CACHE_TTL_HOURS = 12;

    /**
     * @return list<array{loc: string, lastmod: \Illuminate\Support\Carbon|\Carbon\CarbonInterface, changefreq: string, priority: string}>
     */
    public function urls(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addHours(self::CACHE_TTL_HOURS), fn () => $this->build());
    }

    /**
     * @return list<array{loc: string, lastmod: \Illuminate\Support\Carbon|\Carbon\CarbonInterface, changefreq: string, priority: string}>
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
     * @return list<array{loc: string, lastmod: \Illuminate\Support\Carbon|\Carbon\CarbonInterface, changefreq: string, priority: string}>
     */
    protected function build(): array
    {
        $categories = Category::query()
            ->select(['id', 'slug', 'updated_at'])
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('id')
            ->get();

        $products = Product::query()
            ->select(['id', 'slug', 'updated_at'])
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('id')
            ->get();

        $latestUpdate = collect([$categories->max('updated_at'), $products->max('updated_at')])
            ->filter()
            ->max();

        $urls = [
            [
                'loc' => route('home'),
                'lastmod' => $latestUpdate ?? now(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => route('contact.index'),
                'lastmod' => now(),
                'changefreq' => 'yearly',
                'priority' => '0.5',
            ],
        ];

        foreach ($categories as $category) {
            $urls[] = [
                'loc' => route('category.view', $category->slug),
                'lastmod' => $category->updated_at,
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        foreach ($products as $product) {
            $urls[] = [
                'loc' => route('product.view', $product->slug),
                'lastmod' => $product->updated_at,
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ];
        }

        return $urls;
    }
}
