<?php

namespace App\Models\Shop;

use App\Models\Content\Post;
use App\Services\Shop\SitemapService;
use Binafy\LaravelCart\Cartable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Tags\HasTags;

class Product extends Model implements Cartable
{
    protected $fillable = [
        'name',
        'en_name',
        'description',
        'slug',
        'slug_fa',
        'file_path',
        'file_name',
        'weight',
        'x_dimension',
        'y_dimension',
        'z_dimension',
        'category_id',
        'brand_id',
        'unit_id',
    ];

    use HasTags, SoftDeletes;

    protected static function booted(): void
    {
        $invalidateSitemap = fn () => Cache::forget(SitemapService::CACHE_KEY);

        static::saved($invalidateSitemap);
        static::deleted($invalidateSitemap);
        static::restored($invalidateSitemap);
        static::forceDeleted($invalidateSitemap);
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    /**
     * Get the brand that owns the product.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class)->withTrashed();
    }

    /**
     * Get the unit that owns the product.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class)->withTrashed();
    }

    /**
     * Get the colors that belong to the product.
     */
    public function colors(): BelongsToMany
    {
        return $this->belongsToMany(Color::class, 'product_colors')
            ->using(ProductColor::class)
            ->withTimestamps();
    }

    /**
     * Get the warranties that belong to the product.
     */
    public function warranties(): BelongsToMany
    {
        return $this->belongsToMany(Warranty::class, 'product_warranties')
            ->using(ProductWarranty::class)
            ->withTimestamps();
    }

    /**
     * Get the content posts related to the product.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_product')->withTimestamps();
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    /**
     * Get the price fetchers for the product.
     */
    public function priceFetchers(): HasMany
    {
        return $this->hasMany(PriceFetcher::class);
    }

    /**
     * Get the images for the product.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Get the attribute values for the product.
     */
    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    /**
     * Build the cache key for storing the computed default price payload.
     */
    public static function defaultPriceCacheKey(int $productId): string
    {
        return "product:{$productId}:default-price";
    }

    /**
     * Correlated subquery selecting a column from the product's default price row.
     */
    protected function defaultPriceColumnSubquery(string|\Illuminate\Contracts\Database\Query\Expression $column): QueryBuilder
    {
        return DB::table('product_prices')
            ->select($column)
            ->whereColumn('product_prices.product_id', 'products.id')
            ->whereNull('product_prices.deleted_at')
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->limit(1);
    }

    /**
     * Select effective selling price (sale_price when set and lower, else price).
     */
    public function scopeWithEffectivePrice(Builder $query): Builder
    {
        $sub = $this->defaultPriceColumnSubquery(
            DB::raw('COALESCE(NULLIF(sale_price, 0), price)')
        );

        return $query->addSelect(['effective_price' => $sub]);
    }

    public function scopeOrderByEffectivePrice(Builder $query, string $direction = 'asc'): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $query->orderBy('effective_price', $direction);
    }

    public function scopeWhereEffectivePriceBetween(Builder $query, ?float $min, ?float $max): Builder
    {
        $sub = $this->defaultPriceColumnSubquery(
            DB::raw('COALESCE(NULLIF(sale_price, 0), price)')
        );

        if ($min !== null) {
            $query->whereRaw('('.$sub->toSql().') >= ?', [...$sub->getBindings(), $min]);
        }

        if ($max !== null) {
            $query->whereRaw('('.$sub->toSql().') <= ?', [...$sub->getBindings(), $max]);
        }

        return $query;
    }

    /**
     * Filter by default-price availability (quantity > 0 on the preferred price row).
     */
    public function scopeWhereAvailability(Builder $query, bool $available): Builder
    {
        $sub = $this->defaultPriceColumnSubquery('quantity');

        if ($available) {
            return $query->whereRaw('('.$sub->toSql().') > 0', $sub->getBindings());
        }

        return $query->where(function (Builder $outer) use ($sub): void {
            $outer->whereRaw('('.$sub->toSql().') IS NULL', $sub->getBindings())
                ->orWhereRaw('('.$sub->toSql().') <= 0', $sub->getBindings());
        });
    }

    /**
     * Sort by default-price availability (in-stock first when $inStockFirst is true).
     */
    public function scopeOrderByAvailability(Builder $query, bool $inStockFirst = true): Builder
    {
        $sub = $this->defaultPriceColumnSubquery('quantity');

        return $query->orderByRaw(
            'CASE WHEN ('.$sub->toSql().') > 0 THEN 0 ELSE 1 END '.($inStockFirst ? 'asc' : 'desc'),
            $sub->getBindings()
        );
    }

    /**
     * Get the effective regular price based on the product's default price record.
     */
    public function getPriceAttribute(): ?string
    {
        $default = $this->default_price;

        if (! is_array($default)) {
            return null;
        }

        if (($default['available'] ?? false) !== true) {
            return null;
        }

        /** @var \App\Models\Shop\ProductPrice|null $record */
        $record = $default['record'] ?? null;

        return $record?->price;
    }

    /**
     * Get the effective sale price based on the product's default price record.
     */
    public function getSalePriceAttribute(): ?string
    {
        $default = $this->default_price;

        if (! is_array($default)) {
            return null;
        }

        if (($default['available'] ?? false) !== true) {
            return null;
        }

        /** @var \App\Models\Shop\ProductPrice|null $record */
        $record = $default['record'] ?? null;

        return $record?->sale_price;
    }

    /**
     * انتخاب قیمت پیش‌فرض محصول.
     *
     * منطق:
     * - اگر رکوردی با `is_default = 1` وجود داشته باشد همان انتخاب می‌شود.
     * - در غیر این صورت آخرین قیمت ثبت‌شده انتخاب می‌شود.
     * - اگر quantity برابر 0 بود، «این محصول موجود نیست» برگردانده می‌شود.
     *
     * @return array{record: (\App\Models\Shop\ProductPrice|null), available: bool, message: (string|null)}
     */
    public function getDefaultPriceAttribute(): array
    {
        $key = self::defaultPriceCacheKey($this->id);

        return Cache::rememberForever($key, function (): array {
            // ترجیح با قیمت پیش‌فرض است، در غیر این صورت آخرین قیمت ثبت‌شده
            $price = $this->prices()
                ->orderByDesc('is_default')
                ->orderByDesc('created_at')
                ->first();

            if ($price === null) {
                return [
                    'record' => null,
                    'available' => false,
                    'message' => 'هیچ قیمتی برای این محصول ثبت نشده است.',
                ];
            }

            // اگر تعداد موجودی صفر بود اعلام عدم موجودی
            if ((float) $price->quantity <= 0.0) {
                return [
                    'record' => $price,
                    'available' => false,
                    'message' => 'نا موجود',
                ];
            }

            return [
                'record' => $price,
                'available' => true,
                'message' => null,
            ];
        });
    }

    /**
     * Get the price for cart item.
     * This method is required by Cartable interface.
     */
    public function getPrice(): float
    {
        $salePrice = $this->sale_price;
        $regularPrice = $this->price;

        // Return sale price if available, otherwise regular price
        if ($salePrice && $salePrice < $regularPrice) {
            return (float) $salePrice;
        }

        return $regularPrice ? (float) $regularPrice : 0.0;
    }

    /**
     * Canonical public URL: /product/{id}/{slug_fa}.
     */
    public function getUrlAttribute(): string
    {
        return route('product.view', [
            'id' => $this->id,
            'slug' => $this->slug_fa ?: $this->slug,
        ]);
    }
}
