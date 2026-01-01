<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingZone extends Model
{
    use SoftDeletes;

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
