<?php

namespace App\Models\Content;

use App\Models\Shop\Product;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable([
    'title_fa',
    'title_en',
    'color_theme',
    'is_active',
    'sort_order',
])]
class Box extends Model implements HasMedia, Sortable
{
    use InteractsWithMedia, SortableTrait;

    protected $casts = [
        'color_theme' => 'json',
        'is_active' => 'boolean',
    ];

    public $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
    ];

    /**
     * Get the products related to the box.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'box_product');
    }

    /**
     * Get the articles (posts) related to the box.
     */
    public function posts(): MorphToMany
    {
        return $this->morphToMany(Post::class, 'postable');
    }
}
