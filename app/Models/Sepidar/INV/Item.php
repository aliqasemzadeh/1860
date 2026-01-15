<?php

namespace App\Models\Sepidar\INV;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    public $table = 'sepidar_items';
    public $fillable = ['ItemID'];
}
