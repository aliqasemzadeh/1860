<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cheque extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'description',
        'amount',
        'due_at',
    ];

    protected $casts = [
        'amount' => 'decimal:18',
        'due_at' => 'datetime',
    ];
}

