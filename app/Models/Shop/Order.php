<?php

namespace App\Models\Shop;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'currency',
        'subtotal_amount',
        'discount_amount',
        'tax_amount',
        'shipping_method_id',
        'shipping_zone_id',
        'shipping_amount',
        'shipping_estimated_days',
        'total_amount',
        'shipping_address',
        'billing_address',
        'customer_note',
        'meta',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
            'billing_address' => 'array',
            'meta' => 'array',
            'subtotal_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }

    /**
     * Generate a unique order number.
     */
    public static function generateOrderNumber(): string
    {
        $year = now()->year;
        $lastOrder = self::where('order_number', 'like', "ORD-{$year}-%")
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $parts = explode('-', $lastOrder->order_number);
            $number = (int) end($parts) + 1;
        } else {
            $number = 1;
        }

        return sprintf('ORD-%d-%06d', $year, $number);
    }
}
