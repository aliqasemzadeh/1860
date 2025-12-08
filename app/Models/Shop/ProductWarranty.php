<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductWarranty extends Pivot
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_warranties';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'warranty_id',
    ];

    /**
     * Get the product that owns the product warranty.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warranty that owns the product warranty.
     */
    public function warranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class);
    }
}
