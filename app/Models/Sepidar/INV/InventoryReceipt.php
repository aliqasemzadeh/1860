<?php

namespace App\Models\Sepidar\INV;

use App\Models\Sepidar\ACC\DL;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReceipt extends Model
{
    public function dl(): BelongsTo
    {
        return $this->belongsTo(DL::class, 'DLRef', 'DLID');
    }
}
