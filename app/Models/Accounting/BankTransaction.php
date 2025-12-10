<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = ['bank_id', 'user_id', 'amount', 'linker_id', 'linker', 'description'];

    protected $casts = [
        'amount' => 'decimal:5',
    ];

    /**
     * Get the bank that owns this transaction.
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class)->withTrashed();
    }

    /**
     * Get the user that created this transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class)->withTrashed();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($transaction) {
            $transaction->bank->updateBalance();
        });

        static::deleted(function ($transaction) {
            $transaction->bank->updateBalance();
        });
    }
}
