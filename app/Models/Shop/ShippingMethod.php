<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingMethod extends Model
{
    use SoftDeletes;

    public const PICKUP_HANDLES = ['pickup', 'in-person', 'in_person'];

    protected $fillable = [
        'name',
        'handle',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function rates(): HasMany
    {
        return $this->hasMany(\App\Models\Shop\ShippingRate::class);
    }

    public function isPickup(): bool
    {
        return in_array($this->handle, self::PICKUP_HANDLES, true);
    }
}
