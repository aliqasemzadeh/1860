<?php

use App\Models\Shop\Brand;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\ProductPrice;
use App\Models\Shop\Unit;
use Firebase\JWT\JWT;

function createTorobFeedProduct(): Product
{
    $suffix = uniqid();
    $category = Category::create([
        'name' => 'Printer',
        'slug' => 'printer-'.$suffix,
        'slug_fa' => 'printer-fa-'.$suffix,
    ]);
    $brand = Brand::create([
        'name' => 'GPlus',
        'slug' => 'gplus-'.$suffix,
        'slug_fa' => 'gplus-fa-'.$suffix,
    ]);
    $unit = Unit::create(['name' => 'عدد '.$suffix]);
    $product = Product::create([
        'name' => 'پرینتر جی پلاس',
        'en_name' => 'GPlus Printer',
        'description' => '<p>Laser printer</p>',
        'slug' => 'gplus-printer-'.$suffix,
        'slug_fa' => 'gplus-printer-fa-'.$suffix,
        'file_path' => 'products/printer.jpg',
        'file_name' => 'printer.jpg',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'unit_id' => $unit->id,
    ]);
    ProductPrice::create([
        'product_id' => $product->id,
        'price' => 22_000_000,
        'sale_price' => 21_500_000,
        'quantity' => 2,
        'is_default' => true,
    ]);

    return $product;
}

function torobTokenHeaders(string $audience = 'localhost'): array
{
    $keyPair = sodium_crypto_sign_keypair();
    $secretKey = sodium_crypto_sign_secretkey($keyPair);
    $publicKey = sodium_crypto_sign_publickey($keyPair);

    config()->set('services.torob.audience', $audience);
    config()->set('services.torob.public_key', base64_encode('test-prefix-'.$publicKey));

    $token = JWT::encode([
        'aud' => $audience,
        'nbf' => now()->subMinute()->timestamp,
        'exp' => now()->addMinute()->timestamp,
    ], base64_encode($secretKey), 'EdDSA');

    return [
        'X-Torob-Token' => $token,
        'X-Torob-Token-Version' => '1',
    ];
}

test('Torob product API v3 returns the effective product price', function () {
    $product = createTorobFeedProduct();

    $response = $this->withHeaders(torobTokenHeaders())->postJson('/api/torob_api/v3/products', [
        'page' => 1,
        'sort' => 'date_updated_desc',
    ]);

    $response->assertOk()
        ->assertJsonPath('api_version', 'torob_api_v3')
        ->assertJsonPath('total', 1)
        ->assertJsonPath('products.0.page_unique', (string) $product->id)
        ->assertJsonPath('products.0.current_price', 21_500_000)
        ->assertJsonPath('products.0.old_price', 22_000_000)
        ->assertJsonPath('products.0.availability', true)
        ->assertJsonPath('products.0.subtitle', 'GPlus Printer');
});

test('Torob product API v3 rejects requests without a valid token', function () {
    $this->postJson('/api/torob_api/v3/products', [
        'page' => 1,
        'sort' => 'date_added_desc',
    ])->assertUnauthorized();
});

test('Torob product API v3 rejects empty and ambiguous request modes', function () {
    $headers = torobTokenHeaders();

    $this->withHeaders($headers)
        ->postJson('/api/torob_api/v3/products', [])
        ->assertBadRequest();

    $this->withHeaders($headers)
        ->postJson('/api/torob_api/v3/products', [
            'page' => 1,
            'sort' => 'date_added_desc',
            'page_uniques' => ['1'],
        ])
        ->assertBadRequest();
});
