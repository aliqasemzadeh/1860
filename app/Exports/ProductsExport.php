<?php

namespace App\Exports;

use App\Models\Shop\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(protected Collection $products) {}

    public function collection(): Collection
    {
        return $this->products;
    }

    public function headings(): array
    {
        return [
            __('general.id'),
            __('general.name'),
            __('general.en_name'),
            __('general.category'),
            __('general.brand'),
            __('general.unit'),
            __('general.price'),
            __('general.sale_price'),
            __('general.quantity'),
            __('general.stock_status'),
            __('general.created_at'),
            __('general.url'),
        ];
    }

    /**
     * @param  Product  $product
     */
    public function map($product): array
    {
        $default = $product->default_price;
        $record = $default['record'] ?? null;

        return [
            $product->id,
            $product->name,
            $product->en_name,
            $product->category?->name,
            $product->brand?->name,
            $product->unit?->name,
            $record?->price,
            $record?->sale_price,
            $record?->quantity,
            ($default['available'] ?? false) ? __('general.in_stock') : __('general.out_of_stock'),
            jalali($product->created_at),
            $product->url,
        ];
    }
}
