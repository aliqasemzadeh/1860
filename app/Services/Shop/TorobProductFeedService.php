<?php

namespace App\Services\Shop;

use App\Models\Shop\Product;
use App\Models\Shop\ProductPrice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TorobProductFeedService
{
    private const PER_PAGE = 100;

    /**
     * @param  array{page?: int, sort?: string, page_urls?: list<string>, page_uniques?: list<string>}  $input
     * @return array{api_version: string, current_page: int, total: int, max_pages: int, products: list<array<string, mixed>>}
     */
    public function products(array $input): array
    {
        $query = $this->baseQuery();
        $page = 1;

        if (isset($input['page_urls'])) {
            $ids = collect($input['page_urls'])
                ->map(fn (string $url): ?int => $this->productIdFromUrl($url))
                ->filter()
                ->unique()
                ->values();
            $query->whereIn('products.id', $ids);
        } elseif (isset($input['page_uniques'])) {
            $ids = collect($input['page_uniques'])
                ->filter(fn ($id): bool => is_string($id) && ctype_digit($id))
                ->map(fn (string $id): int => (int) $id)
                ->unique()
                ->values();
            $query->whereIn('products.id', $ids);
        } else {
            $page = max(1, (int) $input['page']);
            $sortColumn = $input['sort'] === 'date_updated_desc' ? 'updated_at' : 'created_at';
            $query->orderByDesc("products.{$sortColumn}")->orderByDesc('products.id');
        }

        $total = (clone $query)->count();
        $isPaginated = ! isset($input['page_urls']) && ! isset($input['page_uniques']);
        $maxPages = $isPaginated ? max(1, (int) ceil($total / self::PER_PAGE)) : 1;

        if ($isPaginated) {
            $query->offset(($page - 1) * self::PER_PAGE)->limit(self::PER_PAGE);
        }

        $products = $query->get()
            ->map(fn (Product $product): ?array => $this->transform($product))
            ->filter()
            ->values()
            ->all();

        return [
            'api_version' => 'torob_api_v3',
            'current_page' => $page,
            'total' => $total,
            'max_pages' => $maxPages,
            'products' => $products,
        ];
    }

    private function baseQuery(): Builder
    {
        return Product::query()
            ->active()
            ->whereHas('prices')
            ->with([
                'category',
                'prices' => fn ($query) => $query->orderByDesc('is_default')->orderByDesc('created_at'),
                'prices.warranty',
                'images',
                'attributeValues.attribute.options',
            ]);
    }

    /** @return array<string, mixed>|null */
    private function transform(Product $product): ?array
    {
        /** @var ProductPrice|null $price */
        $price = $product->prices->first();
        if (! $price) {
            return null;
        }

        $regularPrice = (int) $price->price;
        $salePrice = $price->sale_price !== null ? (int) $price->sale_price : null;
        $hasDiscount = $salePrice !== null && $salePrice > 0 && $salePrice < $regularPrice;
        $updatedAt = collect([$product->updated_at, $price->updated_at])->filter()->max();

        $item = [
            'page_unique' => (string) $product->getKey(),
            'page_url' => $product->url,
            'title' => Str::limit($product->name, 500, ''),
            'current_price' => $hasDiscount ? $salePrice : $regularPrice,
            'availability' => (float) $price->quantity > 0,
            'category_name' => Str::limit((string) ($product->category?->name ?? ''), 200, ''),
            'image_links' => $this->imageLinks($product),
            'short_desc' => Str::limit(trim(strip_tags((string) $product->description)), 500, ''),
            'spec' => $this->specifications($product),
            'date_added' => $product->created_at?->toIso8601String(),
            'date_updated' => $updatedAt?->toIso8601String(),
        ];

        if (filled($product->en_name)) {
            $item['subtitle'] = Str::limit($product->en_name, 500, '');
        }

        if ($hasDiscount) {
            $item['old_price'] = $regularPrice;
        }

        if (filled($price->warranty?->name)) {
            $item['guarantee'] = Str::limit($price->warranty->name, 200, '');
        }

        return $item;
    }

    /** @return list<string> */
    private function imageLinks(Product $product): array
    {
        return collect([$product->file_path, ...$product->images->pluck('file_path')->all()])
            ->filter()
            ->unique()
            ->map(fn (string $path): string => url(Storage::url($path)))
            ->values()
            ->all();
    }

    /** @return array<string, mixed> */
    private function specifications(Product $product): array
    {
        return $product->attributeValues
            ->filter(fn ($value): bool => filled($value->attribute?->label))
            ->mapWithKeys(fn ($value): array => [
                Str::limit($value->attribute->label, 200, '') => $value->display_value,
            ])
            ->all();
    }

    private function productIdFromUrl(string $url): ?int
    {
        $path = (string) parse_url($url, PHP_URL_PATH);

        return preg_match('~^/product/(\d+)(?:/|$)~', $path, $matches)
            ? (int) $matches[1]
            : null;
    }
}
