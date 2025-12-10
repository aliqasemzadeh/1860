<?php

namespace App\Enums;

enum RemittanceStatusEnum: string
{
    case Pending = 'pending';
    case Checked = 'checked';
    case Rejected = 'rejected';
    case Transferred = 'transferred';

    /**
     * Get the translation key for the status.
     */
    public function translationKey(): string
    {
        return match ($this) {
            self::Pending => 'remittance_status_pending',
            self::Checked => 'remittance_status_checked',
            self::Rejected => 'remittance_status_rejected',
            self::Transferred => 'remittance_status_transferred',
        };
    }

    /**
     * Get the translated label for the status.
     */
    public function label(): string
    {
        return __('app.'.$this->translationKey());
    }

    /**
     * Get the color for the status badge.
     */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Checked => 'green',
            self::Rejected => 'red',
            self::Transferred => 'blue',
        };
    }

    /**
     * Get all status cases as an array with value, label, and color.
     *
     * @return array<int, array{value: string, label: string, color: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $status) => [
                'value' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
            ],
            self::cases()
        );
    }

    /**
     * Safely get a status enum from a value, returning Pending as default if not found.
     *
     * @param string|null $value
     * @return self
     */
    public static function tryFromSafe(?string $value): self
    {
        if ($value === null) {
            return self::Pending;
        }

        return self::tryFrom($value) ?? self::Pending;
    }
}

