<?php

namespace App\Models\Accounting;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankRemittance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'bank_id',
        'user_id',
        'description',
        'status',
        'draft_amount',
        'final_amount',
        'checked_at',
        'transfer_at',
    ];

    protected $casts = [
        'draft_amount' => 'decimal:5',
        'final_amount' => 'decimal:5',
        'checked_at' => 'datetime',
        'transfer_at' => 'datetime',
    ];

    /**
     * Get the bank that owns this remittance.
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    /**
     * Get the user that created this remittance.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
