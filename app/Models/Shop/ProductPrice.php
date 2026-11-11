<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductPrice extends Model
{
    use SoftDeletes;

    protected $touches = ['product'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'price',
        'sale_price',
        'color_id',
        'warranty_id',
        'quantity',
        'is_default',
    ];

    /**
     * Get the product that owns the price.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the color that owns the price.
     */
    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    /**
     * Get the warranty that owns the price.
     */
    public function warranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class);
    }

    public function torobPriceSetter(): HasOne
    {
        return $this->hasOne(TorobPriceSetter::class);
    }

    public function setareganPriceSetter(): HasOne
    {
        return $this->hasOne(SetareganPriceSetter::class);
    }

    /**
     * Create a new price row (history) and re-point Torob/Setaregan setters to it.
     * The previous row is kept so price history remains queryable.
     *
     * @param  array{
     *     product_id?: int,
     *     color_id?: int|null,
     *     warranty_id?: int|null,
     *     price?: float|int|string,
     *     sale_price?: float|int|string|null,
     *     quantity?: float|int|string,
     *     is_default?: bool|int
     * }  $attributes
     */
    public function supersede(array $attributes = []): self
    {
        return DB::transaction(function () use ($attributes): self {
            $new = static::query()->create([
                'product_id' => $attributes['product_id'] ?? $this->product_id,
                'color_id' => array_key_exists('color_id', $attributes) ? $attributes['color_id'] : $this->color_id,
                'warranty_id' => array_key_exists('warranty_id', $attributes) ? $attributes['warranty_id'] : $this->warranty_id,
                'price' => $attributes['price'] ?? $this->price,
                'sale_price' => array_key_exists('sale_price', $attributes) ? $attributes['sale_price'] : $this->sale_price,
                'quantity' => $attributes['quantity'] ?? $this->quantity,
                'is_default' => $attributes['is_default'] ?? $this->is_default,
            ]);

            TorobPriceSetter::query()
                ->where('product_price_id', $this->id)
                ->update(['product_price_id' => $new->id]);

            SetareganPriceSetter::query()
                ->where('product_price_id', $this->id)
                ->update(['product_price_id' => $new->id]);

            return $new->loadMissing(['product', 'color', 'warranty']);
        });
    }

    protected static function booted(): void
    {
        $forget = function (self $model): void {
            if ($model->product_id) {
                Cache::forget(Product::defaultPriceCacheKey((int) $model->product_id));
            }
        };

        static::saved($forget);
        static::deleted($forget);
        static::restored($forget);
        static::forceDeleted($forget);
    }
}
