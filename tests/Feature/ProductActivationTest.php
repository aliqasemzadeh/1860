<?php

use App\Jobs\Shop\PriceFetcher\FetchPriceJob;
use App\Jobs\Shop\PriceFetcher\UpdatePriceJob;
use App\Jobs\Shop\TorobPriceSetterJob;
use App\Livewire\Main\Order\Cart as CartComponent;
use App\Livewire\Main\Order\Shipping;
use App\Livewire\Main\Product\View as ProductView;
use App\Livewire\Panel\Shop\Product\Index as ProductIndex;
use App\Models\Shop\Brand;
use App\Models\Shop\Category;
use App\Models\Shop\PriceFetcher;
use App\Models\Shop\Product;
use App\Models\Shop\ProductPrice;
use App\Models\Shop\TorobPriceSetter;
use App\Models\Shop\Unit;
use App\Models\User;
use App\Services\Shop\SitemapService;
use App\Support\TorobOfferFetcher;
use Binafy\LaravelCart\Models\Cart;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

function createActivationProduct(array $attributes = []): Product
{
    $suffix = uniqid();
    $category = Category::create([
        'name' => 'Activation category '.$suffix,
        'slug' => 'activation-category-'.$suffix,
        'slug_fa' => 'activation-category-fa-'.$suffix,
    ]);
    $brand = Brand::create([
        'name' => 'Activation brand '.$suffix,
        'slug' => 'activation-brand-'.$suffix,
        'slug_fa' => 'activation-brand-fa-'.$suffix,
    ]);
    $unit = Unit::create(['name' => 'Unit '.$suffix]);

    return Product::create(array_merge([
        'name' => 'Activation product '.$suffix,
        'slug' => 'activation-product-'.$suffix,
        'slug_fa' => 'activation-product-fa-'.$suffix,
        'file_path' => 'products/activation.jpg',
        'file_name' => 'activation.jpg',
        'description' => 'Activation test product',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'unit_id' => $unit->id,
    ], $attributes));
}

function addActivationPrice(Product $product, array $attributes = []): ProductPrice
{
    return ProductPrice::create(array_merge([
        'product_id' => $product->id,
        'price' => 987654,
        'quantity' => 5,
        'is_default' => true,
    ], $attributes));
}

function createActivationUser(): User
{
    return User::create([
        'mobile' => '0912'.random_int(1000000, 9999999),
    ]);
}

test('products are active by default and activation scopes remain explicit', function () {
    $active = createActivationProduct();
    $inactive = createActivationProduct(['is_active' => false]);
    $price = addActivationPrice($inactive);

    expect($active->is_active)->toBeTrue()
        ->and(Product::query()->active()->pluck('id'))->toContain($active->id)
        ->and(Product::query()->active()->pluck('id'))->not->toContain($inactive->id)
        ->and(Product::query()->inactive()->pluck('id'))->toContain($inactive->id)
        ->and($inactive->fresh()->in_stock)->toBeFalse()
        ->and($inactive->fresh()->isPurchasable($price, 1))->toBeFalse();
});

test('product panel separates inactive products and toggles without deleting them', function () {
    $active = createActivationProduct();
    $inactive = createActivationProduct(['is_active' => false]);

    Livewire::test(ProductIndex::class)
        ->assertSee($active->name)
        ->assertDontSee($inactive->name)
        ->set('statusFilter', 'inactive')
        ->assertSee($inactive->name)
        ->assertDontSee($active->name)
        ->call('toggleActive', $inactive->id);

    expect($inactive->fresh()->is_active)->toBeTrue()
        ->and($inactive->fresh()->deleted_at)->toBeNull();
});

test('inactive product page is visible without prices or purchase controls', function () {
    $product = createActivationProduct(['is_active' => false]);
    addActivationPrice($product);

    $this->get($product->url)
        ->assertOk()
        ->assertSee($product->name)
        ->assertSee(__('general.out_of_stock'))
        ->assertSee('noindex, follow', false)
        ->assertDontSee('987,654')
        ->assertDontSee('wire:click="addToCart"', false);
});

test('inactive products are excluded from feeds and sitemap', function () {
    $product = createActivationProduct(['is_active' => false]);
    addActivationPrice($product);

    $this->getJson('/list')
        ->assertOk()
        ->assertJsonMissing(['id' => (string) $product->id]);

    expect(collect(app(SitemapService::class)->refresh())->pluck('loc'))
        ->not->toContain($product->url);
});

test('inactive products cannot be added to a cart', function () {
    $user = createActivationUser();
    $product = createActivationProduct(['is_active' => false]);
    addActivationPrice($product);

    Livewire::actingAs($user)
        ->test(ProductView::class, ['id' => $product->id, 'slug' => $product->slug_fa])
        ->call('addToCart');

    expect(Cart::query()->where('user_id', $user->id)->exists())->toBeFalse();
});

test('a product disabled after being added remains visible but blocks checkout', function () {
    $user = createActivationUser();
    $product = createActivationProduct();
    $price = addActivationPrice($product);
    $cart = Cart::query()->create(['user_id' => $user->id]);
    $cart->storeItem([
        'itemable' => $product,
        'quantity' => 1,
        'options' => json_encode(['price_id' => $price->id]),
    ]);
    $product->update(['is_active' => false]);

    Livewire::actingAs($user)
        ->test(CartComponent::class)
        ->assertSee($product->name)
        ->assertSee(__('general.out_of_stock'))
        ->assertSet('hasUnavailableItems', true)
        ->assertSet('totalAmount', 0);

    Livewire::actingAs($user)
        ->test(Shipping::class)
        ->assertRedirect(route('order.cart'));
});

test('bulk price fetching dispatches jobs only for active products', function () {
    Queue::fake();
    $active = createActivationProduct();
    $inactive = createActivationProduct(['is_active' => false]);
    $activeFetcher = PriceFetcher::create([
        'product_id' => $active->id,
        'type' => 'digikala',
        'url' => 'https://example.com/active',
    ]);
    PriceFetcher::create([
        'product_id' => $inactive->id,
        'type' => 'digikala',
        'url' => 'https://example.com/inactive',
    ]);

    app(UpdatePriceJob::class)->handle();

    Queue::assertPushed(FetchPriceJob::class, 1);
    Queue::assertPushed(fn (FetchPriceJob $job): bool => $job->priceFetcher->is($activeFetcher));
});

test('Torob price setting skips inactive products in the command and queued job', function () {
    Queue::fake();
    $product = createActivationProduct(['is_active' => false]);
    $price = addActivationPrice($product);
    $fetcher = PriceFetcher::create([
        'product_id' => $product->id,
        'type' => 'torob',
        'url' => 'https://torob.com/p/inactive-product/',
    ]);
    $setter = TorobPriceSetter::create([
        'price_fetcher_id' => $fetcher->id,
        'product_price_id' => $price->id,
        'own_shop_names' => ['Shop'],
        'step_amount' => 1000,
        'min_price' => 900000,
        'max_price' => 1100000,
        'is_active' => true,
    ]);

    $this->artisan('shop:sync-torob-prices')->assertSuccessful();
    Queue::assertNotPushed(TorobPriceSetterJob::class);

    $offerFetcher = Mockery::mock(TorobOfferFetcher::class);
    $offerFetcher->shouldNotReceive('cheapestCompetitor');
    (new TorobPriceSetterJob($setter))->handle($offerFetcher);

    expect($setter->fresh()->status)->toBe(TorobPriceSetter::STATUS_PRODUCT_UNAVAILABLE);
});
