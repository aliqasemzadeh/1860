<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsImportTemplateExport implements FromArray, ShouldAutoSize, WithHeadings
{
    public function headings(): array
    {
        return [
            __('general.name'),
            __('general.en_name'),
            __('general.description'),
            __('general.category'),
            __('general.brand'),
            __('general.unit'),
            __('general.price'),
            __('general.sale_price'),
            __('general.quantity'),
            __('general.weight'),
            __('general.image_url'),
        ];
    }

    public function array(): array
    {
        return [
            [
                __('general.import_sample_product_name'),
                'Sample Product',
                __('general.import_sample_description'),
                __('general.import_sample_category'),
                __('general.import_sample_brand'),
                __('general.import_sample_unit'),
                '1000000',
                '900000',
                '10',
                '500',
                'https://example.com/image.jpg',
            ],
        ];
    }
}
