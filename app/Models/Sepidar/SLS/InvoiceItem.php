<?php

namespace App\Models\Sepidar\SLS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    public $table = 'sepidar_invoice_items';
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'InvoiceRef', 'InvoiceId');
    }
}
