<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    protected $fillable = [
        'shipping_method_id',
        'shipping_zone_id',
        'rate_type',
        'amount',
        'min_weight',
        'max_weight',
        'min_price',
        'max_price',
        'estimated_days',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'min_weight' => 'decimal:2',
        'max_weight' => 'decimal:2',
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function method(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Shop\ShippingMethod::class, 'shipping_method_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Shop\ShippingZone::class, 'shipping_zone_id');
    }
}
