<?php

namespace App\Support\Seo;

use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\ProductPrice;
use App\Settings\ContactSettings;
use App\Settings\GeneralSettings;
use App\Settings\SocialSettings;
use Illuminate\Support\Facades\Storage;

class SeoSchema
{
    /**
     * @return array<string, mixed>
     */
    public static function organization(): array
    {
        $general = app(GeneralSettings::class);
        $contact = app(ContactSettings::class);
        $socials = array_values(app(SocialSettings::class)->active());

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $general->title ?: config('app.name'),
            'url' => route('home'),
        ];

        $logo = $general->logoUrl() ?? asset('images/logo.png');
        if ($logo) {
            $schema['logo'] = url($logo);
        }

        if ($socials !== []) {
            $schema['sameAs'] = $socials;
        }

        $telephone = $contact->mobile ?: $contact->phone;
        $contactPoint = array_filter([
            '@type' => 'ContactPoint',
            'telephone' => $telephone,
            'email' => $contact->email,
            'contactType' => 'customer service',
        ], fn ($value) => filled($value));

        if (count($contactPoint) > 2) {
            $schema['contactPoint'] = $contactPoint;
        }

        if (filled($contact->address)) {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $contact->address,
                'addressCountry' => 'IR',
            ];
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    public static function website(): array
    {
        $general = app(GeneralSettings::class);

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $general->title ?: config('app.name'),
            'url' => route('home'),
            'inLanguage' => 'fa-IR',
        ];
    }

    /**
     * @param  iterable<int, array{file_path?: string}|object>  $images
     * @return array<string, mixed>
     */
    public static function product(Product $product, ?ProductPrice $price, iterable $images, string $description): array
    {
        $canonicalUrl = $product->url;

        $imageUrls = [];
        foreach ($images as $image) {
            $path = is_array($image) ? ($image['file_path'] ?? null) : ($image->file_path ?? null);
            if (filled($path)) {
                $imageUrls[] = url(Storage::url($path));
            }
        }

        if ($imageUrls === [] && filled($product->file_path)) {
            $imageUrls[] = url(Storage::url($product->file_path));
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => implode(' ', array_filter([$product->name, $product->en_name])),
            'description' => $description,
            'sku' => (string) ($product->sku ?? $product->id),
            'mpn' => (string) ($product->sku ?? $product->id),
            'url' => $canonicalUrl,
        ];

        if ($product->tags && $product->tags->isNotEmpty()) {
            $schema['keywords'] = $product->tags->pluck('name')->implode(', ');
        }

        if ($imageUrls !== []) {
            $schema['image'] = count($imageUrls) === 1 ? $imageUrls[0] : $imageUrls;
        }

        $schema['brand'] = [
            '@type' => 'Brand',
            'name' => $product->brand?->name ?? 'Default Brand',
        ];

        if ($product->category) {
            $schema['category'] = $product->category->name;
        }

        if ($price) {
            $finalPrice = ($price->sale_price && $price->sale_price < $price->price)
                ? $price->sale_price
                : $price->price;

            $schema['offers'] = [
                '@type' => 'Offer',
                'url' => $canonicalUrl,
                'priceCurrency' => config('seo.currency', 'IRR'),
                // Prices are stored in Toman; Schema.org expects IRR (Rial).
                'price' => (string) ((float) $finalPrice * (float) config('seo.currency_multiplier', 10)),
                'priceValidUntil' => now()->addDays(30)->toDateString(),
                'availability' => ((float) $price->quantity > 0)
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => app(GeneralSettings::class)->title ?: config('app.name'),
                ],
            ];
        } else {
            $defaultPriceRecord = $product->default_price['record'] ?? null;
            $rawPrice = $defaultPriceRecord?->sale_price ?? $defaultPriceRecord?->price ?? $product->price ?? 0;
            $quantity = $defaultPriceRecord?->quantity ?? $product->stock ?? 0;

            $schema['offers'] = [
                '@type' => 'Offer',
                'url' => $canonicalUrl,
                'priceCurrency' => config('seo.currency', 'IRR'),
                'price' => (string) ((float) $rawPrice * (float) config('seo.currency_multiplier', 10)),
                'priceValidUntil' => now()->addDays(30)->toDateString(),
                'availability' => ((float) $quantity > 0)
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => app(GeneralSettings::class)->title ?: config('app.name'),
                ],
            ];
        }

        return $schema;
    }

    /**
     * @param  iterable<int, Product>  $products
     * @return array<string, mixed>
     */
    public static function collectionPage(Category $category, iterable $products, string $description): array
    {
        $itemList = [];
        $position = 1;

        foreach ($products as $product) {
            $itemList[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'url' => $product->url,
                'name' => $product->name,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $category->name,
            'description' => $description,
            'url' => $category->url,
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => count($itemList),
                'itemListElement' => $itemList,
            ],
        ];
    }

    /**
     * @param  list<array{name: string, url: string}>  $items
     * @return array<string, mixed>
     */
    public static function breadcrumbs(array $items): array
    {
        $elements = [];

        foreach (array_values($items) as $index => $item) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function contactPage(): array
    {
        $organization = self::organization();
        unset($organization['@context']);

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ContactPage',
            'name' => __('general.contact_us'),
            'url' => route('contact.index'),
            'mainEntity' => $organization,
        ];
    }
}
