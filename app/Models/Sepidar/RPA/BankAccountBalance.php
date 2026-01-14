<?php

namespace App\Models\Sepidar\RPA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccountBalance extends Model
{

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'BankAccountRef', 'BankAccountID');
    }
}
