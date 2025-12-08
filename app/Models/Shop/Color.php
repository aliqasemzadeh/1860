<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Color extends Model
{
    use SoftDeletes;

    /**
     * Mass assignable attributes.
     */
    public $fillable = ['name', 'slug', 'slug_fa', 'hex'];

    /**
     * Model casts.
     */
    public function casts(): array
    {
        return [];
    }

    /**
     * Get the products that have this color.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_colors')
            ->using(ProductColor::class)
            ->withTimestamps();
    }
}
