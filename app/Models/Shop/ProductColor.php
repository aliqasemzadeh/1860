<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductColor extends Pivot
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_colors';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'color_id',
    ];

    /**
     * Get the product that owns the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product that owns the color.
     */
    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }


}
