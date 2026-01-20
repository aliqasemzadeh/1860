<?php

namespace App\Models\Sepidar\INV;

use App\Models\Sepidar\ACC\DL;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InventoryReceipt extends Model
{
    public $table = 'sepidar_inventory_receipts';
    public function dl(): BelongsTo
    {
        return $this->belongsTo(DL::class, 'DelivererDLRef', 'DLId');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryReceiptItem::class, 'InventoryReceiptRef', 'InventoryReceiptID');
    }

    public function lastItem(): HasOne
    {
        return $this->hasOne(
            InventoryReceiptItem::class,
            'InventoryReceiptRef',
            'InventoryReceiptID'
        )->latest('InventoryReceiptItemID');
    }

    public function maxPriceItem(): HasOne
    {
        return $this->hasOne(
                    InventoryReceiptItem::class,
                    'InventoryReceiptRef',
                    'InventoryReceiptID'
                )->orderByDesc('Price')
            ->orderByDesc('InventoryReceiptItemID');
    }

    public function maxFeeItem(): HasOne
    {
        return $this->hasOne(
                    InventoryReceiptItem::class,
                    'InventoryReceiptRef',
                    'InventoryReceiptID'
                )->orderByDesc('Fee')
            ->orderByDesc('InventoryReceiptItemID'); // برای رفع تساوی
    }
}
