<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'description', 'sort_order', 'meta'];

    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Get all remittances for this bank.
     */
    public function remittances(): HasMany
    {
        return $this->hasMany(BankRemittance::class);
    }

    /**
     * Get all transactions for this bank.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }
}
