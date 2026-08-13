<?php

namespace App\Livewire\Main\Category;

use App\Models\Shop\Brand;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Support\Seo\Seo;
use Livewire\Attributes\Computed;
use Livewire\Component;

class View extends Component
{
    public $id = null;

    public $slug = null;

    public $sortBy = 'created_at';

    public $sortDirection = 'desc';

    public $brandId = null;

    public $minPrice = null;

    public $maxPrice = null;

    public string $stockFilter = 'available';

    public function mount($id = null, $slug = null)
    {
        $this->id = $id;
        $this->slug = $slug;

        $category = $this->category;

        if (! $category) {
            abort(404);
        }

        $canonicalSlug = $category->slug_fa ?: $category->slug;
        if ($this->slug === null || $this->slug !== $canonicalSlug) {
            return redirect()->to($category->url, 301);
        }
    }

    #[Computed]
    public function category()
    {
        if (! $this->id) {
            return null;
        }

        return Category::query()
            ->with(['children'])
            ->where('id', $this->id)
            ->first();
    }

    #[Computed]
    public function brands()
    {
        if (! $this->category) {
            return collect();
        }

        // Get all category IDs (including children)
        $categoryIds = $this->getCategoryIds();

        $brandIds = Product::query()
            ->whereIn('category_id', $categoryIds)
            ->whereNotNull('brand_id')
            ->distinct()
            ->pluck('brand_id');

        return Brand::query()
            ->whereIn('id', $brandIds)
            ->orderBy('name', 'asc')
            ->get();
    }

    #[Computed]
    public function products()
    {
        if (! $this->category) {
            return collect();
        }

        // Get all category IDs (including children)
        $categoryIds = $this->getCategoryIds();

        $query = Product::query()
            ->with(['colors', 'warranties', 'brand', 'category'])
            ->whereIn('category_id', $categoryIds);

        // Filter by brand
        if ($this->brandId) {
            $query->where('brand_id', $this->brandId);
        }

        // Filter by price range (using default_price)
        // This is complex because price is computed, so we'll filter after getting products
        // For now, we'll skip price filtering in query and do it in PHP

        // Sorting
        switch ($this->sortBy) {
            case 'price_asc':
                // Sort by price ascending (default price)
                $query->orderBy('created_at', 'desc'); // Default, will sort in PHP
                break;
            case 'price_desc':
                // Sort by price descending (default price)
                $query->orderBy('created_at', 'desc'); // Default, will sort in PHP
                break;
            case 'name':
                $query->orderBy('name', $this->sortDirection);
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', $this->sortDirection);
                break;
        }

        $products = $query->get();

        // Apply price filtering
        if ($this->minPrice !== null || $this->maxPrice !== null) {
            $products = $products->filter(function ($product) {
                $price = $product->sale_price ?? $product->price;
                if ($price === null) {
                    return false; // Skip products without price when filtering by price
                }

                // Filter by price range
                if ($this->minPrice !== null && $price < $this->minPrice) {
                    return false;
                }
                if ($this->maxPrice !== null && $price > $this->maxPrice) {
                    return false;
                }

                return true;
            });
        }

        // Sort by price if needed
        if ($this->sortBy === 'price_asc') {
            $products = $products->sortBy(function ($product) {
                return (float) ($product->sale_price ?? $product->price ?? PHP_INT_MAX);
            })->values();
        } elseif ($this->sortBy === 'price_desc') {
            $products = $products->sortByDesc(function ($product) {
                $price = (float) ($product->sale_price ?? $product->price ?? 0);
                return $price > 0 ? $price : PHP_INT_MIN;
            })->values();
        }

        return $products
            ->when($this->stockFilter === 'available', fn ($items) => $items->filter(
                fn ($product) => ($product->default_price['available'] ?? false) === true
            ))
            ->when($this->stockFilter === 'unavailable', fn ($items) => $items->filter(
                fn ($product) => ($product->default_price['available'] ?? false) !== true
            ))
            ->values();
    }

    protected function getCategoryIds(): array
    {
        if (! $this->category) {
            return [];
        }

        $ids = [$this->category->id];

        // Include children categories
        foreach ($this->category->children as $child) {
            $ids[] = $child->id;
        }

        return $ids;
    }

    public function clearFilters(): void
    {
        $this->brandId = null;
        $this->minPrice = null;
        $this->maxPrice = null;
        $this->stockFilter = 'available';
        $this->sortBy = 'created_at';
        $this->sortDirection = 'desc';
    }

    #[Computed]
    public function seo(): Seo
    {
        $filtered = filled($this->brandId)
            || $this->minPrice !== null
            || $this->maxPrice !== null
            || $this->stockFilter !== 'available';

        return Seo::category(
            $this->category,
            $this->products,
            filtered: $filtered,
        );
    }

    public function render()
    {
        if (! $this->category) {
            abort(404);
        }

        return view('livewire.main.category.view');
    }
}
