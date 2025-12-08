<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'slug',
        'slug_fa',
        'file_path',
        'file_name',
        'weight',
        'x_dimension',
        'y_dimension',
        'z_dimension',
        'category_id',
        'brand_id',
        'unit_id',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    /**
     * Get the brand that owns the product.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class)->withTrashed();
    }

    /**
     * Get the unit that owns the product.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class)->withTrashed();
    }

    /**
     * Get the colors that belong to the product.
     */
    public function colors(): BelongsToMany
    {
        return $this->belongsToMany(Color::class, 'product_colors')
            ->using(ProductColor::class)
            ->withTimestamps();
    }

    /**
     * Get the warranties that belong to the product.
     */
    public function warranties(): BelongsToMany
    {
        return $this->belongsToMany(Warranty::class, 'product_warranties')
            ->using(ProductWarranty::class)
            ->withTimestamps();
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function getPriceAttribute()
    {

    }

    public function getSalePriceAttribute()
    {

    }
}
