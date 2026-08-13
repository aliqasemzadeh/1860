<?php

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeValue extends Model
{
    protected $fillable = [
        'product_id',
        'attribute_id',
        'value_text',
        'value_number',
        'value_bool',
        'value_date',
        'value_json',
    ];

    protected $casts = [
        'value_number' => 'decimal:6',
        'value_bool' => 'boolean',
        'value_date' => 'date',
        'value_json' => 'array',
    ];

    /**
     * Get the product that owns the attribute value.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the attribute that owns the value.
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Get the display value based on attribute type.
     */
    public function getDisplayValueAttribute(): mixed
    {
        if (! $this->relationLoaded('attribute') || ! $this->attribute) {
            return $this->value_text ?? '-';
        }

        $value = match ($this->attribute->type) {
            'text', 'textarea' => $this->value_text,
            'number' => $this->value_number !== null ? number_format($this->value_number, 0) : null,
            'boolean' => $this->value_bool !== null ? ($this->value_bool ? __('general.yes') : __('general.no')) : null,
            'date' => jalali($this->value_date, 'Y/m/d', '-'),
            'select' => $this->getSelectDisplayValue(),
            'multiselect' => $this->getMultiselectDisplayValue(),
            default => $this->value_text,
        };

        return $value ?? '-';
    }

    /**
     * Get display value for select type attributes.
     */
    private function getSelectDisplayValue(): ?string
    {
        if (! $this->value_json) {
            return null;
        }

        $selectedValue = is_array($this->value_json) ? ($this->value_json[0] ?? null) : $this->value_json;
        if (! $selectedValue) {
            return null;
        }

        $option = $this->attribute->options->firstWhere('value', $selectedValue);

        return $option?->label ?? $selectedValue;
    }

    /**
     * Get display value for multiselect type attributes.
     */
    private function getMultiselectDisplayValue(): ?array
    {
        if (! $this->value_json || ! is_array($this->value_json)) {
            return null;
        }

        $labels = [];
        foreach ($this->value_json as $value) {
            $option = $this->attribute->options->firstWhere('value', $value);
            $labels[] = $option?->label ?? $value;
        }

        return $labels;
    }
}
