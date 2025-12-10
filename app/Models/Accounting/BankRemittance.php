<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankRemittance extends Model
{
    use SoftDeletes;

    protected $fillable = ['bank_id', 'amount'];

    /**
     * Get the bank that owns this remittance.
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }
}
