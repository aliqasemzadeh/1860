<?php

use App\Models\Shop\Product;
use App\Services\Shop\ProductImageSeoService;
use Morilog\Jalali\Jalalian;

if (! function_exists('jalali')) {
    function jalali(?\DateTimeInterface $date, string $format = 'Y/m/d H:i', string $empty = '-'): string
    {
        if ($date === null) {
            return $empty;
        }

        return Jalalian::forge($date)->format($format);
    }
}

if (! function_exists('product_image_alt')) {
    function product_image_alt(Product|string|null $productOrName): string
    {
        return app(ProductImageSeoService::class)->imageAlt($productOrName);
    }
}
