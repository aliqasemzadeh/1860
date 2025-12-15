<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    protected $fillable = [
        'name',
        'countries',
        'states',
        'cities',
        'areas',
    ];

    protected $casts = [
        'countries' => 'array',
        'states' => 'array',
        'cities' => 'array',
        'areas' => 'array',
    ];

    public function rates(): HasMany
    {
        return $this->hasMany(\App\Models\Shop\ShippingRate::class);
    }
}
