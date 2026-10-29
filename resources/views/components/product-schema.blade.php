@props(['product', 'price' => null, 'images' => []])

@php
    use App\Support\Seo\Seo;
    use App\Support\Seo\SeoSchema;

    $description = Seo::clean(
        strip_tags((string) ($product->description ?: implode(' ', array_filter([$product->name, $product->en_name])))),
        (int) config('seo.description_limit', 160)
    );

    $schema = SeoSchema::product(
        $product,
        $price,
        $images !== [] ? $images : [['file_path' => $product->file_path]],
        $description,
    );
@endphp

<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
