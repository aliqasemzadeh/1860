<?php

namespace App\Models\Sepidar\INV;

use App\Models\Sepidar\SLS\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReceiptItem extends Model
{
    public $table = 'sepidar_inventory_receipt_items';
    public function receipt(): BelongsTo
    {
        return $this->belongsTo(InventoryReceipt::class, 'InventoryReceiptRef', 'InventoryReceiptID');
    }
}
