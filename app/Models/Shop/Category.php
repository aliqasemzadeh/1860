<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    public $fillable = ['name', 'slug', 'slug_fa', 'icon', 'sort_order', 'main_category_id'];

    public function main_category()
    {
        $this->belongsTo(Category::class, 'main_category_id')->withTrashed();
    }
}
