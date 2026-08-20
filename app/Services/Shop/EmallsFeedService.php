<?php

namespace App\Services\Shop;

use App\Models\Shop\Category;
use App\Models\Shop\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmallsFeedService
{
    /**
     * @return array{success: bool, products: list<array>, total_items: int, pages_count: int, item_per_page: int, page_num: int}
     */
    public function paginate(int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), (int) config('emalls.max_per_page', 100));

        $query = Product::query()
            ->whereHas('prices')
            ->with([
                'category.main_category',
                'prices' => fn ($q) => $q->orderByDesc('is_default')->orderByDesc('created_at'),
                'prices.color',
                'prices.warranty',
            ]);

        $total = $query->count();
        $pagesCount = $total > 0 ? (int) ceil($total / $perPage) : 0;

        $products = $query
            ->orderBy('id')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        $categories = $this->loadCategoryMap();

        $items = $products->map(fn (Product $product) => $this->transform($product, $categories))->all();

        return [
            'success' => true,
            'products' => $items,
            'total_items' => $total,
            'pages_count' => $pagesCount,
            'item_per_page' => $perPage,
            'page_num' => $page,
        ];
    }

    /**
     * @param  Collection<int, Category>  $categories  keyed by id
     */
    private function transform(Product $product, Collection $categories): array
    {
        $price = $product->prices->first();

        $hasDiscount = $price && $price->sale_price && $price->sale_price < $price->price;
        $currentPrice = $hasDiscount ? (int) $price->sale_price : (int) ($price?->price ?? 0);

        $item = [
            'title' => Str::limit($product->name, 512, ''),
            'id' => (string) $product->id,
            'price' => $currentPrice,
            'category' => $product->category_id ? $this->categoryPath($product->category_id, $categories) : '',
            'image' => $this->imageUrl($product),
            'is_available' => $price && (float) $price->quantity > 0,
            'url' => $product->url,
        ];

        if ($hasDiscount) {
            $item['old_price'] = (int) $price->price;
        }

        $colorName = $price?->color?->name;
        if (filled($colorName)) {
            $item['color'] = $colorName;
        }

        $guaranteeName = $price?->warranty?->name ?? null;
        if (filled($guaranteeName)) {
            $item['guarantee'] = $guaranteeName;
        }

        return $item;
    }

    private function imageUrl(Product $product): ?string
    {
        if (filled($product->file_path)) {
            return url(Storage::url($product->file_path));
        }

        return null;
    }

    /**
     * @return Collection<int, Category> keyed by id
     */
    private function loadCategoryMap(): Collection
    {
        return Category::query()
            ->select(['id', 'name', 'main_category_id'])
            ->get()
            ->keyBy('id');
    }

    private function categoryPath(int $categoryId, Collection $categories): string
    {
        $parts = [];
        $id = $categoryId;
        $visited = [];

        while ($id && isset($categories[$id]) && ! isset($visited[$id])) {
            $visited[$id] = true;
            $cat = $categories[$id];
            $parts[] = $cat->name;
            $id = $cat->main_category_id;
        }

        return implode(' / ', array_reverse($parts));
    }
}
