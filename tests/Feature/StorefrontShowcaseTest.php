<?php

use App\Livewire\Main\Content\Box\Index as BoxIndex;
use App\Livewire\Main\Dashboard\Index as HomePage;
use App\Livewire\Main\Product\View as ProductView;
use App\Models\Content\Box;
use App\Models\Content\Post;
use App\Models\Shop\Brand;
use App\Models\Shop\Category;
use App\Models\Shop\Color;
use App\Models\Shop\Product;
use App\Models\Shop\ProductImage;
use App\Models\Shop\ProductPrice;
use App\Models\Shop\Unit;
use App\Models\Shop\Warranty;
use App\Models\User;
use Binafy\LaravelCart\Models\Cart;
use Livewire\Livewire;

function createShowcaseProduct(array $attributes = [], array $price = []): Product
{
    $suffix = uniqid();

    $category = Category::create([
        'name' => 'مانیتور '.$suffix,
        'slug' => 'monitor-'.$suffix,
        'slug_fa' => 'مانیتور-'.$suffix,
    ]);

    $brand = Brand::create([
        'name' => 'GPlus',
        'slug' => 'gplus-'.$suffix,
        'slug_fa' => 'جی-پلاس-'.$suffix,
    ]);

    $unit = Unit::create(['name' => 'دستگاه '.$suffix]);

    $product = Product::create(array_merge([
        'name' => 'مانیتور جی‌پلاس '.$suffix,
        'en_name' => 'GPlus Monitor '.$suffix,
        'slug' => 'gplus-monitor-'.$suffix,
        'slug_fa' => 'مانیتور-جی-پلاس-'.$suffix,
        'file_path' => 'products/monitor.jpg',
        'file_name' => 'monitor.jpg',
        'description' => '<p>توضیحات نمایشی محصول</p>',
        'weight' => 4200,
        'x_dimension' => 61,
        'y_dimension' => 45,
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'unit_id' => $unit->id,
    ], $attributes));

    ProductPrice::create(array_merge([
        'product_id' => $product->id,
        'price' => 12_500_000,
        'sale_price' => 10_900_000,
        'quantity' => 4,
        'is_default' => true,
    ], $price));

    return $product;
}

test('home page renders hero bento, brand islands and exactly two editorial posts', function () {
    $product = createShowcaseProduct();

    $box = Box::create([
        'title_fa' => 'جی‌پلاس',
        'title_en' => 'GPlus',
        'is_active' => true,
        'color_theme' => ['bg' => '#18181b', 'text' => '#ffffff', 'accent' => '#14b8a6'],
    ]);
    $box->products()->attach($product->id);

    foreach (range(1, 3) as $i) {
        Post::create([
            'title' => "مقاله شماره {$i}",
            'slug' => "post-{$i}-".uniqid(),
            'summary' => "خلاصه مقاله {$i}",
            'content' => '<p>'.str_repeat('کلمه ', 450).'</p>',
            'status' => 'published',
            'published_at' => now()->subMinutes($i),
        ]);
    }

    Livewire::test(HomePage::class)
        ->assertStatus(200)
        ->assertSee(__('general.home_hero_title'))
        ->assertSee(__('general.special_offers'))
        ->assertSee(__('general.featured_articles'))
        ->assertSee('مقاله شماره 1')
        ->assertSee('مقاله شماره 2')
        ->assertDontSee('مقاله شماره 3')
        ->assertSee('3 '.__('general.min_read'));

    Livewire::test(BoxIndex::class)
        ->assertStatus(200)
        ->assertSee('جی‌پلاس')
        ->assertSee(__('general.view_brand_products', ['brand' => 'جی‌پلاس']))
        ->assertSee($product->name)
        ->assertSee('10,900,000')
        ->assertSee('quickAdd('.$product->id.')', false);
});

test('brand island quick add puts the default variant into the cart', function () {
    $product = createShowcaseProduct();
    $user = User::create(['mobile' => '0912'.random_int(1000000, 9999999)]);

    Livewire::actingAs($user)
        ->test(BoxIndex::class)
        ->call('quickAdd', $product->id);

    $cart = Cart::query()->where('user_id', $user->id)->first();

    expect($cart)->not->toBeNull()
        ->and($cart->items()->count())->toBe(1)
        ->and($cart->items()->first()->quantity)->toBe(1);
});

test('brand island quick add redirects to the product page when a variant must be chosen', function () {
    $product = createShowcaseProduct();
    $color = Color::create(['name' => 'مشکی', 'slug' => 'black-'.uniqid(), 'slug_fa' => 'مشکی-'.uniqid(), 'hex' => '#000000']);
    $product->colors()->attach($color->id);

    $user = User::create(['mobile' => '0912'.random_int(1000000, 9999999)]);

    Livewire::actingAs($user)
        ->test(BoxIndex::class)
        ->call('quickAdd', $product->id)
        ->assertRedirect($product->url);

    expect(Cart::query()->where('user_id', $user->id)->exists())->toBeFalse();
});

test('product page renders the showcase gallery, highlights, specs and sticky buy bar', function () {
    $product = createShowcaseProduct();

    ProductImage::create([
        'product_id' => $product->id,
        'file_path' => 'products/monitor-side.jpg',
        'file_name' => 'monitor-side.jpg',
    ]);

    $color = Color::create(['name' => 'نقره‌ای', 'slug' => 'silver-'.uniqid(), 'slug_fa' => 'نقره‌ای-'.uniqid(), 'hex' => '#c0c0c0']);
    $warranty = Warranty::create(['name' => 'گارانتی ۱۸ ماهه', 'slug' => 'warranty-18-'.uniqid(), 'slug_fa' => 'گارانتی-۱۸-'.uniqid()]);
    $product->colors()->attach($color->id);
    $product->warranties()->attach($warranty->id);

    ProductPrice::create([
        'product_id' => $product->id,
        'color_id' => $color->id,
        'warranty_id' => $warranty->id,
        'price' => 13_000_000,
        'quantity' => 2,
    ]);

    Livewire::test(ProductView::class, ['id' => $product->id, 'slug' => $product->slug_fa])
        ->assertStatus(200)
        ->assertSee($product->name)
        ->assertSee($product->en_name)
        ->assertSee('GPlus')
        ->assertSee(__('general.in_stock_ready'))
        ->assertSee(__('general.you_save'))
        ->assertSee('10,900,000')
        ->assertSee('13٪')
        ->assertSee(__('general.expert_review'))
        ->assertSee(__('general.technical_specifications'))
        ->assertSee(__('general.user_reviews'))
        ->assertSee(__('general.physical_specs'))
        ->assertSee('4,200 '.__('general.gram'))
        ->assertSee('نقره‌ای')
        ->assertSee('گارانتی ۱۸ ماهه')
        ->assertSee(__('general.product_gallery'))
        ->assertSee('monitor-side.jpg')
        ->assertSeeHtml('lg:hidden')
        ->set('selectedWarrantyId', $warranty->id)
        ->call('selectColor', $color->id)
        ->assertSee('13,000,000');
});
