<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function translationKey(): string
    {
        return match ($this) {
            self::Pending => 'order_status_pending',
            self::Processing => 'order_status_processing',
            self::Shipped => 'order_status_shipped',
            self::Delivered => 'order_status_delivered',
            self::Cancelled => 'order_status_cancelled',
        };
    }

    public function label(): string
    {
        return __('app.'.$this->translationKey());
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'orange',
            self::Processing => 'sky',
            self::Shipped => 'purple',
            self::Delivered => 'green',
            self::Cancelled => 'danger',
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Pending => in_array($target, [self::Processing, self::Cancelled], true),
            self::Processing => $target === self::Shipped,
            self::Shipped => $target === self::Delivered,
            self::Delivered, self::Cancelled => false,
        };
    }

    public static function tryFromSafe(?string $value): self
    {
        if ($value === null) {
            return self::Pending;
        }

        return self::tryFrom($value) ?? self::Pending;
    }
}
