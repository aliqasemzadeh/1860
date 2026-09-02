<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TorobPriceSetter extends Model
{
    public const STATUS_IDLE = 'idle';

    public const STATUS_UPDATED = 'updated';

    public const STATUS_UNCHANGED = 'unchanged';

    public const STATUS_FLOOR_REACHED = 'floor_reached';

    public const STATUS_NO_COMPETITOR = 'no_competitor';

    public const STATUS_PRODUCT_UNAVAILABLE = 'product_unavailable';

    public const STATUS_FETCH_FAILED = 'fetch_failed';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'price_fetcher_id',
        'product_price_id',
        'own_shop_names',
        'step_amount',
        'min_price',
        'max_price',
        'is_active',
        'status',
        'last_competitor_shop',
        'last_competitor_price',
        'last_target_price',
        'last_applied_price',
        'last_checked_at',
        'last_changed_at',
        'last_error',
    ];

    protected $casts = [
        'own_shop_names' => 'array',
        'step_amount' => 'integer',
        'min_price' => 'integer',
        'max_price' => 'integer',
        'is_active' => 'boolean',
        'last_competitor_price' => 'integer',
        'last_target_price' => 'integer',
        'last_applied_price' => 'integer',
        'last_checked_at' => 'datetime',
        'last_changed_at' => 'datetime',
    ];

    public function priceFetcher(): BelongsTo
    {
        return $this->belongsTo(PriceFetcher::class);
    }

    public function productPrice(): BelongsTo
    {
        return $this->belongsTo(ProductPrice::class);
    }
}
