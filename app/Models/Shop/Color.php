<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
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
}
