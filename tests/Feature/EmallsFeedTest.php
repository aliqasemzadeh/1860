<?php

use App\Models\Shop\Brand;
use App\Models\Shop\Category;
use App\Models\Shop\Color;
use App\Models\Shop\Product;
use App\Models\Shop\ProductPrice;
use App\Models\Shop\Unit;
use App\Models\Shop\Warranty;

function createEmallsProduct(array $attributes = []): Product
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

    $unit = Unit::create(['name' => 'عدد']);

    $id = uniqid();

    return Product::create(array_merge([
        'name' => 'Product '.$id,
        'slug' => 'product-'.$id,
        'slug_fa' => 'product-fa-'.$id,
        'file_path' => 'products/sample.jpg',
        'file_name' => 'sample.jpg',
        'description' => 'Test product',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'unit_id' => $unit->id,
    ], $attributes));
}

test('emalls feed returns valid json structure', function () {
    $product = createEmallsProduct();
    ProductPrice::create([
        'product_id' => $product->id,
        'price' => 500000,
        'quantity' => 10,
        'is_default' => 1,
    ]);

    $response = $this->get('/list?page=1&item_per_page=50');

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/json; charset=utf-8')
        ->assertJsonStructure([
            'success',
            'products' => [['title', 'id', 'price', 'category', 'image', 'is_available', 'url']],
            'total_items',
            'pages_count',
            'item_per_page',
            'page_num',
        ])
        ->assertJson(['success' => true, 'page_num' => 1]);
});

test('emalls feed shows discount with old_price', function () {
    $product = createEmallsProduct();
    ProductPrice::create([
        'product_id' => $product->id,
        'price' => 600000,
        'sale_price' => 500000,
        'quantity' => 5,
        'is_default' => 1,
    ]);

    $response = $this->getJson('/list');

    $item = $response->json('products.0');
    expect($item['price'])->toBe(500000)
        ->and($item['old_price'])->toBe(600000);
});

test('emalls feed marks out of stock product', function () {
    $product = createEmallsProduct();
    ProductPrice::create([
        'product_id' => $product->id,
        'price' => 300000,
        'quantity' => 0,
        'is_default' => 1,
    ]);

    $response = $this->getJson('/list');

    $item = $response->json('products.0');
    expect($item['is_available'])->toBeFalse();
});

test('emalls feed excludes products without any price', function () {
    createEmallsProduct();

    $response = $this->getJson('/list');

    expect($response->json('products'))->toBeEmpty()
        ->and($response->json('total_items'))->toBe(0);
});

test('emalls feed pagination metadata is correct', function () {
    for ($i = 0; $i < 3; $i++) {
        $p = createEmallsProduct();
        ProductPrice::create([
            'product_id' => $p->id,
            'price' => 100000,
            'quantity' => 1,
            'is_default' => 1,
        ]);
    }

    $response = $this->getJson('/list?page=1&item_per_page=2');

    expect($response->json('total_items'))->toBe(3)
        ->and($response->json('pages_count'))->toBe(2)
        ->and($response->json('item_per_page'))->toBe(2)
        ->and($response->json('page_num'))->toBe(1)
        ->and($response->json('products'))->toHaveCount(2);
});

test('emalls feed caps item_per_page to max', function () {
    $response = $this->getJson('/list?item_per_page=999');

    expect($response->json('item_per_page'))->toBe(100);
});

test('emalls feed includes color and guarantee when available', function () {
    $product = createEmallsProduct();
    $color = Color::create(['name' => 'مشکی', 'slug' => 'black', 'slug_fa' => 'meshki', 'hex' => '#000000']);
    $warranty = Warranty::create(['name' => '18 ماه گارانتی', 'slug' => '18-month', 'slug_fa' => '18-mah']);

    ProductPrice::create([
        'product_id' => $product->id,
        'price' => 400000,
        'quantity' => 3,
        'is_default' => 1,
        'color_id' => $color->id,
        'warranty_id' => $warranty->id,
    ]);

    $response = $this->getJson('/list');

    $item = $response->json('products.0');
    expect($item['color'])->toBe('مشکی')
        ->and($item['guarantee'])->toBe('18 ماه گارانتی');
});

test('emalls feed builds category path with parent', function () {
    $parent = Category::create(['name' => 'الکترونیک', 'slug' => 'electronics', 'slug_fa' => 'electronic-fa']);
    $child = Category::create(['name' => 'موبایل', 'slug' => 'mobile', 'slug_fa' => 'mobile-fa', 'main_category_id' => $parent->id]);

    $brand = Brand::create(['name' => 'B', 'slug' => 'b', 'slug_fa' => 'b-fa']);
    $unit = Unit::create(['name' => 'عدد']);

    $product = Product::create([
        'name' => 'Phone',
        'slug' => 'phone',
        'slug_fa' => 'phone-fa',
        'file_path' => 'products/p.jpg',
        'file_name' => 'p.jpg',
        'description' => 'test',
        'category_id' => $child->id,
        'brand_id' => $brand->id,
        'unit_id' => $unit->id,
    ]);

    ProductPrice::create([
        'product_id' => $product->id,
        'price' => 100000,
        'quantity' => 1,
        'is_default' => 1,
    ]);

    $response = $this->getJson('/list');

    $item = collect($response->json('products'))->firstWhere('id', (string) $product->id);
    expect($item['category'])->toBe('الکترونیک / موبایل');
});

test('emalls feed accepts POST method', function () {
    $response = $this->postJson('/list');

    $response->assertStatus(200)
        ->assertJson(['success' => true]);
});
