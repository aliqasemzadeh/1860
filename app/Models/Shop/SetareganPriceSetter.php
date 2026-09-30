<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SetareganPriceSetter extends Model
{
    public const STATUS_IDLE = 'idle';

    public const STATUS_UPDATED = 'updated';

    public const STATUS_UNCHANGED = 'unchanged';

    public const STATUS_FLOOR_REACHED = 'floor_reached';

    public const STATUS_OUT_OF_STOCK = 'out_of_stock';

    public const STATUS_NO_PRICE = 'no_price';

    public const STATUS_PAGE_NOT_FOUND = 'page_not_found';

    public const STATUS_PRODUCT_UNAVAILABLE = 'product_unavailable';

    public const STATUS_FETCH_FAILED = 'fetch_failed';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'price_fetcher_id',
        'product_price_id',
        'margin_amount',
        'min_price',
        'max_price',
        'default_quantity',
        'is_active',
        'status',
        'last_supplier_price',
        'last_supplier_available',
        'last_target_price',
        'last_applied_price',
        'last_stock_action',
        'last_checked_at',
        'last_changed_at',
        'last_stock_changed_at',
        'last_error',
    ];

    protected $casts = [
        'margin_amount' => 'integer',
        'min_price' => 'integer',
        'max_price' => 'integer',
        'default_quantity' => 'integer',
        'is_active' => 'boolean',
        'last_supplier_price' => 'integer',
        'last_supplier_available' => 'boolean',
        'last_target_price' => 'integer',
        'last_applied_price' => 'integer',
        'last_checked_at' => 'datetime',
        'last_changed_at' => 'datetime',
        'last_stock_changed_at' => 'datetime',
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
