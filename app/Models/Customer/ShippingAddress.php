<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingAddress extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'province_id',
        'city_id',
        'address',
        'postal_code',
        'latitude',
        'longitude',
        'emergency_contact',
        'is_default',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    /**
     * Get the user that owns the shipping address.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get province name from language file.
     */
    public function getProvinceNameAttribute(): ?string
    {
        $provinces = require lang_path('fa/provinces.php');
        return $provinces[$this->province_id] ?? null;
    }

    /**
     * Get city name from language file.
     */
    public function getCityNameAttribute(): ?string
    {
        $cities = require lang_path('fa/cities.php');
        return $cities[$this->province_id][$this->city_id] ?? null;
    }
}
