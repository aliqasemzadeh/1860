<?php

use App\Ai\Agents\CatalogAttributeSchema;
use App\Ai\Agents\CatalogProductAttributes;
use App\Ai\Agents\CatalogProductContent;
use App\Livewire\Panel\Shop\Product\Attributes as ProductAttributes;
use App\Livewire\Panel\Shop\Product\Edit;
use App\Models\Shop\Attribute;
use App\Models\Shop\AttributeGroup;
use App\Models\Shop\AttributeOption;
use App\Models\Shop\Brand;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\ProductAttributeValue;
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

test('catalog ai can suggest and apply attribute schema to a category', function () {
    CatalogAttributeSchema::fake([
        [
            'groups' => [
                [
                    'name' => 'مشخصات فنی',
                    'attributes' => [
                        [
                            'key' => 'print_speed',
                            'label' => 'سرعت چاپ',
                            'type' => 'number',
                            'is_required' => true,
                            'options' => [],
                        ],
                        [
                            'key' => 'print_technology',
                            'label' => 'فناوری چاپ',
                            'type' => 'select',
                            'is_required' => true,
                            'options' => [
                                ['value' => 'laser', 'label' => 'لیزری'],
                                ['value' => 'inkjet', 'label' => 'جوهرافشان'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $product = createCatalogAiProduct();
    $category = $product->category;
    $service = app(CatalogAiService::class);

    $schema = $service->suggestCategoryAttributes('پرینتر', $category);

    expect($schema)->toHaveCount(1)
        ->and($schema[0]['attributes'])->toHaveCount(2);

    $ids = $service->applyCategoryAttributeSchema($category, $schema);

    expect($ids)->toHaveCount(2)
        ->and($category->fresh()->attributes)->toHaveCount(2)
        ->and(Attribute::query()->where('key', 'print_speed')->exists())->toBeTrue()
        ->and(AttributeOption::query()->where('value', 'laser')->exists())->toBeTrue();
});

test('catalog ai fills product attribute values without saving', function () {
    $product = createCatalogAiProduct(['en_name' => 'HP LaserJet 1200']);
    $group = AttributeGroup::create(['name' => 'فنی', 'sort_order' => 1]);

    $speed = Attribute::create([
        'attribute_group_id' => $group->id,
        'key' => 'print_speed',
        'label' => 'سرعت چاپ',
        'type' => 'number',
        'is_required' => false,
        'sort_order' => 1,
    ]);

    $tech = Attribute::create([
        'attribute_group_id' => $group->id,
        'key' => 'print_technology',
        'label' => 'فناوری چاپ',
        'type' => 'select',
        'is_required' => false,
        'sort_order' => 2,
    ]);

    AttributeOption::create([
        'attribute_id' => $tech->id,
        'value' => 'laser',
        'label' => 'لیزری',
        'sort_order' => 1,
    ]);
    AttributeOption::create([
        'attribute_id' => $tech->id,
        'value' => 'inkjet',
        'label' => 'جوهرافشان',
        'sort_order' => 2,
    ]);

    $product->category->attributes()->attach([
        $speed->id => ['is_required' => false, 'sort_order' => 1],
        $tech->id => ['is_required' => false, 'sort_order' => 2],
    ]);

    CatalogProductAttributes::fake([
        [
            'values' => [
                [
                    'attribute_id' => $speed->id,
                    'value' => null,
                    'number_value' => 14,
                    'boolean_value' => null,
                    'list_value' => null,
                ],
                [
                    'attribute_id' => $tech->id,
                    'value' => 'laser',
                    'number_value' => null,
                    'boolean_value' => null,
                    'list_value' => null,
                ],
            ],
        ],
    ]);

    $values = app(CatalogAiService::class)->generateProductAttributeValues($product, 'HP1200');

    expect($values[$speed->id])->toBe(14.0)
        ->and($values[$tech->id])->toBe('laser');

    // Invalid select option must be discarded.
    CatalogProductAttributes::fake([
        [
            'values' => [
                [
                    'attribute_id' => $tech->id,
                    'value' => 'invalid-option',
                    'number_value' => null,
                    'boolean_value' => null,
                    'list_value' => null,
                ],
            ],
        ],
    ]);

    $invalid = app(CatalogAiService::class)->generateProductAttributeValues($product, 'HP1200');
    expect($invalid[$tech->id])->toBeNull();

    $user = User::create([
        'mobile' => '0912'.random_int(1000000, 9999999),
    ]);

    CatalogProductAttributes::fake([
        [
            'values' => [
                [
                    'attribute_id' => $speed->id,
                    'value' => null,
                    'number_value' => 14,
                    'boolean_value' => null,
                    'list_value' => null,
                ],
                [
                    'attribute_id' => $tech->id,
                    'value' => 'laser',
                    'number_value' => null,
                    'boolean_value' => null,
                    'list_value' => null,
                ],
            ],
        ],
    ]);

    Livewire::actingAs($user)
        ->test(ProductAttributes::class, ['id' => $product->id])
        ->set('modelHint', 'HP1200')
        ->call('generateAttributeValues')
        ->assertSet('attributeValues.'.$speed->id, 14.0)
        ->assertSet('attributeValues.'.$tech->id, 'laser');

    expect(ProductAttributeValue::query()->where('product_id', $product->id)->count())->toBe(0);
});

test('ai specs wizard applies schema then fills values', function () {
    CatalogAttributeSchema::fake([
        [
            'groups' => [
                [
                    'name' => 'چاپ',
                    'attributes' => [
                        [
                            'key' => 'color_support',
                            'label' => 'پشتیبانی رنگ',
                            'type' => 'boolean',
                            'is_required' => false,
                            'options' => [],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $product = createCatalogAiProduct(['name' => 'پرینتر HP', 'en_name' => 'HP1200']);
    $user = User::create([
        'mobile' => '0912'.random_int(1000000, 9999999),
    ]);

    $component = Livewire::actingAs($user)
        ->test(ProductAttributes::class, ['id' => $product->id])
        ->set('productKind', 'پرینتر')
        ->call('suggestAttributeSchema')
        ->assertSet('schemaPreview.0.attributes.0.key', 'color_support')
        ->call('applyAttributeSchema')
        ->assertSet('aiStep', 2);

    $attribute = Attribute::query()->where('key', 'color_support')->first();
    expect($attribute)->not->toBeNull()
        ->and($product->category->fresh()->attributes->pluck('id'))->toContain($attribute->id);

    CatalogProductAttributes::fake([
        [
            'values' => [
                [
                    'attribute_id' => $attribute->id,
                    'value' => null,
                    'number_value' => null,
                    'boolean_value' => true,
                    'list_value' => null,
                ],
            ],
        ],
    ]);

    $component
        ->set('modelHint', 'HP1200')
        ->call('generateAttributeValues')
        ->assertSet('attributeValues.'.$attribute->id, true);

    expect(ProductAttributeValue::query()->where('product_id', $product->id)->count())->toBe(0);
});
