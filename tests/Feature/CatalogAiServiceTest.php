<?php

use App\Ai\Agents\CatalogProductContent;
use App\Livewire\Panel\Shop\Product\Edit;
use App\Models\Shop\Brand;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\Unit;
use App\Models\User;
use App\Services\Shop\CatalogAiService;
use Livewire\Livewire;

function createCatalogAiProduct(array $attributes = []): Product
{
    $category = Category::create([
        'name' => 'موبایل',
        'slug' => 'mobile-'.uniqid(),
        'slug_fa' => 'موبایل-'.uniqid(),
        'main_category_id' => 0,
    ]);

    $brand = Brand::create([
        'name' => 'Samsung',
        'slug' => 'samsung-'.uniqid(),
        'slug_fa' => 'سامسونگ-'.uniqid(),
    ]);

    $unit = Unit::create([
        'name' => 'عدد',
    ]);

    $id = uniqid();

    return Product::create(array_merge([
        'name' => 'گوشی سامسونگ '.$id,
        'en_name' => '',
        'description' => '',
        'slug' => 'product-'.$id,
        'slug_fa' => 'محصول-'.$id,
        'file_path' => 'products/sample.jpg',
        'file_name' => 'sample.jpg',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'unit_id' => $unit->id,
        'weight' => 0,
        'x_dimension' => 0,
        'y_dimension' => 0,
        'z_dimension' => 0,
    ], $attributes));
}

test('catalog ai service returns structured product content', function () {
    CatalogProductContent::fake([
        [
            'en_name' => 'Samsung Galaxy S24',
            'description' => '<p>گوشی سامسونگ گلکسی اس ۲۴ با نمایشگر روشن و عملکرد سریع.</p>',
            'tags' => ['سامسونگ', 'گلکسی', 'گوشی'],
        ],
    ]);

    $product = createCatalogAiProduct();

    $result = app(CatalogAiService::class)->generateProductContent($product, [
        'name' => $product->name,
        'brand' => 'Samsung',
        'category' => 'موبایل',
    ]);

    expect($result['en_name'])->toBe('Samsung Galaxy S24')
        ->and($result['description'])->toContain('گوشی سامسونگ')
        ->and($result['tags'])->toBe(['سامسونگ', 'گلکسی', 'گوشی']);

    CatalogProductContent::assertPrompted(fn ($prompt) => $prompt->contains($product->name));
});

test('product edit generateWithAi fills form without saving', function () {
    CatalogProductContent::fake([
        [
            'en_name' => 'Generated English Name',
            'description' => '<p>توضیح تولید شده برای سئو</p>',
            'tags' => ['تگ یک', 'تگ دو'],
        ],
    ]);

    $product = createCatalogAiProduct([
        'en_name' => 'Old English',
        'description' => '<p>old</p>',
    ]);
    $product->syncTags(['قدیمی']);

    $user = User::create([
        'mobile' => '0912'.random_int(1000000, 9999999),
    ]);

    Livewire::actingAs($user)
        ->test(Edit::class)
        ->call('assignData', $product->id)
        ->assertSet('en_name', 'Old English')
        ->call('generateWithAi')
        ->assertSet('en_name', 'Generated English Name')
        ->assertSet('description', '<p>توضیح تولید شده برای سئو</p>')
        ->assertSet('tags', ['تگ یک', 'تگ دو']);

    $product->refresh();

    expect($product->en_name)->toBe('Old English')
        ->and($product->description)->toBe('<p>old</p>')
        ->and($product->tags->pluck('name')->all())->toBe(['قدیمی']);
});

test('product edit generateWithAi requires a product name', function () {
    $product = createCatalogAiProduct();
    $user = User::create([
        'mobile' => '0912'.random_int(1000000, 9999999),
    ]);

    Livewire::actingAs($user)
        ->test(Edit::class)
        ->call('assignData', $product->id)
        ->set('name', '')
        ->call('generateWithAi')
        ->assertSet('description', $product->description);
});
