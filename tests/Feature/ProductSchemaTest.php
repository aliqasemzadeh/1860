<?php

use App\Models\Shop\Brand;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\ProductPrice;
use App\Models\Shop\Unit;
use App\Support\Seo\SeoSchema;
use Illuminate\Support\Facades\Blade;

function createTestProduct(array $attributes = []): Product
{
    $category = Category::create([
        'name' => 'Category '.uniqid(),
        'slug' => 'cat-'.uniqid(),
        'slug_fa' => 'cat-fa-'.uniqid(),
    ]);

    $brand = Brand::create([
        'name' => 'Brand '.uniqid(),
        'slug' => 'brand-'.uniqid(),
        'slug_fa' => 'brand-fa-'.uniqid(),
    ]);

    $unit = Unit::create([
        'name' => 'عدد',
    ]);

    $id = uniqid();

    return Product::create(array_merge([
        'name' => 'Product '.$id,
        'slug' => 'product-'.$id,
        'slug_fa' => 'product-fa-'.$id,
        'file_path' => 'products/sample.jpg',
        'file_name' => 'sample.jpg',
        'description' => 'Product description',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'unit_id' => $unit->id,
    ], $attributes));
}

test('product model accessors work as expected', function () {
    $brand = Brand::create(['name' => 'Acme Brand', 'slug' => 'acme-brand', 'slug_fa' => 'acme-brand-fa']);
    $category = Category::create(['name' => 'Electronics', 'slug' => 'electronics', 'slug_fa' => 'electronics-fa']);
    $unit = Unit::create(['name' => 'عدد']);

    $product = Product::create([
        'name' => 'Awesome Smartphone',
        'en_name' => 'Awesome Smartphone EN',
        'slug' => 'awesome-smartphone',
        'slug_fa' => 'awesome-smartphone-fa',
        'description' => '<p>Super fast and durable smartphone.</p>',
        'file_path' => 'products/sample.jpg',
        'file_name' => 'sample.jpg',
        'brand_id' => $brand->id,
        'category_id' => $category->id,
        'unit_id' => $unit->id,
    ]);

    ProductPrice::create([
        'product_id' => $product->id,
        'price' => 500000,
        'sale_price' => 450000,
        'quantity' => 10,
        'is_default' => 1,
    ]);

    expect($product->image_url)->not->toBeNull()
        ->and($product->sku)->toBe((string) $product->id)
        ->and($product->stock)->toBe(10.0)
        ->and($product->in_stock)->toBeTrue()
        ->and($product->price)->toBe('500000');
});

test('product schema component renders valid json-ld with required fields', function () {
    $brand = Brand::create(['name' => 'TechCorp', 'slug' => 'techcorp', 'slug_fa' => 'techcorp-fa']);
    $product = createTestProduct([
        'name' => 'Super Gadget',
        'description' => 'A wonderful gadget for all purposes.',
        'brand_id' => $brand->id,
    ]);

    ProductPrice::create([
        'product_id' => $product->id,
        'price' => 1000000,
        'quantity' => 5,
        'is_default' => 1,
    ]);

    $html = Blade::render('<x-product-schema :product="$product" />', ['product' => $product]);

    expect($html)->toContain('application/ld+json')
        ->and($html)->toContain('"@type": "Product"')
        ->and($html)->toContain('"name": "Super Gadget"')
        ->and($html)->toContain('"sku": "'.$product->id.'"')
        ->and($html)->toContain('"@type": "Brand"')
        ->and($html)->toContain('"name": "TechCorp"')
        ->and($html)->toContain('"@type": "Offer"')
        ->and($html)->toContain('"priceCurrency": "IRR"')
        ->and($html)->toContain('"price": "1000000"')
        ->and($html)->toContain('https://schema.org/InStock');
});

test('product schema handles out of stock product', function () {
    $product = createTestProduct([
        'name' => 'Soldout Item',
        'brand_id' => 0,
    ]);

    ProductPrice::create([
        'product_id' => $product->id,
        'price' => 200000,
        'quantity' => 0,
        'is_default' => 1,
    ]);

    $html = Blade::render('<x-product-schema :product="$product" />', ['product' => $product]);

    expect($html)->toContain('https://schema.org/OutOfStock')
        ->and($html)->toContain('"name": "Default Brand"');
});

test('product view page contains product schema in head', function () {
    $product = createTestProduct([
        'name' => 'Wireless Headphones',
    ]);

    ProductPrice::create([
        'product_id' => $product->id,
        'price' => 3500000,
        'quantity' => 15,
        'is_default' => 1,
    ]);

    $response = $this->get($product->url);

    $response->assertStatus(200)
        ->assertSee('application/ld+json', false)
        ->assertSee('"@type": "Product"', false)
        ->assertSee('Wireless Headphones')
        ->assertSee('"@type": "Offer"', false)
        ->assertSee('3500000');
});

test('seo schema generator creates product schema with offers and brand', function () {
    $brand = Brand::create(['name' => 'Apple', 'slug' => 'apple', 'slug_fa' => 'apple-fa']);
    $product = createTestProduct([
        'name' => 'iPhone 15',
        'brand_id' => $brand->id,
    ]);

    $schema = SeoSchema::product($product, null, [], 'iPhone 15 description');

    expect($schema['@type'])->toBe('Product')
        ->and($schema['brand']['name'])->toBe('Apple')
        ->and($schema['offers'])->toBeArray()
        ->and($schema['offers']['@type'])->toBe('Offer')
        ->and($schema['offers']['priceCurrency'])->toBe('IRR')
        ->and($schema['offers']['availability'])->toBe('https://schema.org/OutOfStock');
});
