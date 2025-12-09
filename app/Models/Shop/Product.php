<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
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

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    /**
     * Build the cache key for storing the computed default price payload.
     */
    public static function defaultPriceCacheKey(int $productId): string
    {
        return "product:{$productId}:default-price";
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
}
