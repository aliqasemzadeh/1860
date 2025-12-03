<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
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
}
