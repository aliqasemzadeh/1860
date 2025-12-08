<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warranty extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    public $fillable = ['name', 'slug', 'slug_fa'];

    /**
     * Model casts.
     */
    public function casts(): array
    {
        return [];
    }

    /**
     * Get the products that have this warranty.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_warranties')
            ->using(ProductWarranty::class)
            ->withTimestamps();
    }
}
