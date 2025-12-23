<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Remittance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'description',
        'account_balance',
        'payment',
    ];

    protected $casts = [
        'account_balance' => 'decimal:18',
        'payment' => 'decimal:18',
    ];
}
