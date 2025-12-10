<?php

namespace App\Enums;

enum TransactionTypeEnum: string
{
    case DayInit = 'DayInit';
    case Income = 'Income';
    case Expense = 'Expense';
    case Remittance = 'Remittance';
    case Transfer = 'Transfer';
    case Receive = 'Receive';
    case Payment = 'Payment';

    /**
     * Get the translation key for the transaction type.
     */
    public function translationKey(): string
    {
        return match ($this) {
            self::DayInit => 'transaction_type_day_init',
            self::Income => 'transaction_type_income',
            self::Expense => 'transaction_type_expense',
            self::Remittance => 'transaction_type_remittance',
            self::Transfer => 'transaction_type_transfer',
            self::Receive => 'transaction_type_receive',
            self::Payment => 'transaction_type_payment',
        };
    }

    /**
     * Get the translated label for the transaction type.
     */
    public function label(): string
    {
        return __('app.'.$this->translationKey());
    }

    /**
     * Get the color for the transaction type badge.
     */
    public function color(): string
    {
        return match ($this) {
            self::DayInit => 'zinc',
            self::Income => 'green',
            self::Expense => 'red',
            self::Remittance => 'blue',
            self::Transfer => 'indigo',
            self::Receive => 'emerald',
            self::Payment => 'orange',
        };
    }

    /**
     * Get all transaction type cases as an array with value, label, and color.
     *
     * @return array<int, array{value: string, label: string, color: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $type) => [
                'value' => $type->value,
                'label' => $type->label(),
                'color' => $type->color(),
            ],
            self::cases()
        );
    }

    /**
     * Safely get a transaction type enum from a value, returning DayInit as default if not found.
     *
     * @param string|null $value
     * @return self
     */
    public static function tryFromSafe(?string $value): self
    {
        if ($value === null) {
            return self::DayInit;
        }

        return self::tryFrom($value) ?? self::DayInit;
    }
}

