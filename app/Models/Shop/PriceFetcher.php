<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceFetcher extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'type',
        'url',
        'last_price',
        'last_fetched_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_fetched_at' => 'datetime',
        'last_price' => 'integer',
    ];

    /**
     * Get the product that owns the price fetcher.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the type label.
     */
    public function getTypeLabelAttribute(): string
    {
        if (!$this->type) {
            return '';
        }

        return match ($this->type) {
            'digikala' => __('app.price_fetcher_type_digikala'),
            'fafait' => __('app.price_fetcher_type_fafait'),
            'markazi' => __('app.price_fetcher_type_markazi'),
            default => $this->type,
        };
    }
}
