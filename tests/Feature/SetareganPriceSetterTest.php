<?php

use App\Jobs\Notification\SendBaleMessageJob;
use App\Jobs\Shop\SetareganPriceSetterJob;
use App\Livewire\Panel\Shop\Product\PriceFetchers;
use App\Models\Shop\Brand;
use App\Models\Shop\Category;
use App\Models\Shop\PriceFetcher;
use App\Models\Shop\Product;
use App\Models\Shop\ProductPrice;
use App\Models\Shop\SetareganPriceSetter;
use App\Models\Shop\TorobPriceSetter;
use App\Models\Shop\Unit;
use App\Models\User;
use App\Settings\BaleSettings;
use App\Support\SetareganFetchException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

function createSetareganPricingRule(array $setterOverrides = [], array $priceOverrides = []): array
{
    $suffix = uniqid();
    $category = Category::create([
        'name' => 'Setaregan category '.$suffix,
        'slug' => 'setaregan-category-'.$suffix,
        'slug_fa' => 'setaregan-category-fa-'.$suffix,
    ]);
    $brand = Brand::create([
        'name' => 'Setaregan brand '.$suffix,
        'slug' => 'setaregan-brand-'.$suffix,
        'slug_fa' => 'setaregan-brand-fa-'.$suffix,
    ]);
    $unit = Unit::create(['name' => 'عدد '.$suffix]);
    $product = Product::create([
        'name' => 'Setaregan product '.$suffix,
        'slug' => 'setaregan-product-'.$suffix,
        'slug_fa' => 'setaregan-product-fa-'.$suffix,
        'file_path' => 'products/setaregan-test.jpg',
        'file_name' => 'setaregan-test.jpg',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'unit_id' => $unit->id,
    ]);
    $price = ProductPrice::create(array_merge([
        'product_id' => $product->id,
        'price' => 22_000_000,
        'sale_price' => 21_000_000,
        'quantity' => 5,
        'is_default' => true,
    ], $priceOverrides));
    $fetcher = PriceFetcher::create([
        'product_id' => $product->id,
        'type' => 'setaregan',
        'url' => 'https://setaregan.co/product/sample-'.$suffix,
    ]);
    $setter = SetareganPriceSetter::create(array_merge([
        'price_fetcher_id' => $fetcher->id,
        'product_price_id' => $price->id,
        'margin_amount' => 500_000,
        'min_price' => 18_000_000,
        'max_price' => 24_000_000,
        'default_quantity' => 1,
        'is_active' => true,
    ], $setterOverrides));

    return compact('product', 'price', 'fetcher', 'setter');
}

function setareganInStockHtml(int $price = 20_000_000): string
{
    return <<<HTML
    <html><body>
    <h1>محصول تست ستارگان</h1>
    <script type="application/ld+json">
    {"@type":"Product","offers":{"@type":"Offer","price":"{$price}","availability":"https://schema.org/InStock"}}
    </script>
    <button>افزودن به سبد خرید</button>
    <div>محصولات مشابه</div>
    <span>ناموجود</span>
    </body></html>
    HTML;
}

function setareganOutOfStockHtml(?int $price = 20_000_000): string
{
    $priceMeta = $price !== null
        ? "<meta name=\"product_price\" content=\"{$price}\">"
        : '';

    return <<<HTML
    <html><body>
    <h1>محصول تست ستارگان</h1>
    {$priceMeta}
    <script type="application/ld+json">
    {"@type":"Product","offers":{"@type":"Offer","price":"{$price}","availability":"https://schema.org/OutOfStock"}}
    </script>
    <p>ناموجود</p>
    </body></html>
    HTML;
}

function fakeSetareganPage(string $html, int $status = 200): void
{
    Http::fake([
        'setaregan.co/*' => Http::response($html, $status),
        'www.setaregan.co/*' => Http::response($html, $status),
    ]);
}

function enableSetareganBaleNotifications(): void
{
    $settings = app(BaleSettings::class);
    $settings->bot_token = 'test-token';
    $settings->chat_id = '12345';
    $settings->save();
}

beforeEach(function () {
    Cache::flush();
});

test('setaregan pricing rule applies supplier price minus step to both price and sale_price', function () {
    Queue::fake([SendBaleMessageJob::class]);
    enableSetareganBaleNotifications();

    ['price' => $price, 'fetcher' => $fetcher, 'setter' => $setter] = createSetareganPricingRule();
    fakeSetareganPage(setareganInStockHtml(20_000_000));

    SetareganPriceSetterJob::dispatchSync($setter);

    $price->refresh();

    expect((int) $price->price)->toBe(19_500_000)
        ->and((int) $price->sale_price)->toBe(19_500_000)
        ->and($fetcher->fresh()->last_price)->toBe(20_000_000)
        ->and($setter->fresh()->status)->toBe(SetareganPriceSetter::STATUS_UPDATED)
        ->and($setter->fresh()->last_supplier_available)->toBeTrue();

    Queue::assertPushed(SendBaleMessageJob::class);
});

test('setaregan pricing rule caps a high target at the configured maximum', function () {
    ['price' => $price, 'setter' => $setter] = createSetareganPricingRule();
    fakeSetareganPage(setareganInStockHtml(30_000_000));

    SetareganPriceSetterJob::dispatchSync($setter);

    expect((int) $price->fresh()->price)->toBe(24_000_000)
        ->and((int) $price->fresh()->sale_price)->toBe(24_000_000)
        ->and($setter->fresh()->last_target_price)->toBe(24_000_000)
        ->and($setter->fresh()->status)->toBe(SetareganPriceSetter::STATUS_UPDATED);
});

test('setaregan pricing rule keeps the current price when floor is reached', function () {
    Queue::fake([SendBaleMessageJob::class]);

    ['price' => $price, 'setter' => $setter] = createSetareganPricingRule([
        'min_price' => 20_000_000,
    ]);
    fakeSetareganPage(setareganInStockHtml(17_000_000));

    SetareganPriceSetterJob::dispatchSync($setter);

    expect((int) $price->fresh()->price)->toBe(22_000_000)
        ->and((int) $price->fresh()->sale_price)->toBe(21_000_000)
        ->and($setter->fresh()->status)->toBe(SetareganPriceSetter::STATUS_FLOOR_REACHED)
        ->and($setter->fresh()->last_target_price)->toBe(16_500_000);

    Queue::assertNotPushed(SendBaleMessageJob::class);
});

test('setaregan pricing rule zeroes quantity when supplier is out of stock', function () {
    Queue::fake([SendBaleMessageJob::class]);
    enableSetareganBaleNotifications();

    ['price' => $price, 'setter' => $setter] = createSetareganPricingRule();
    fakeSetareganPage(setareganOutOfStockHtml());

    SetareganPriceSetterJob::dispatchSync($setter);

    expect((float) $price->fresh()->quantity)->toBe(0.0)
        ->and((int) $price->fresh()->price)->toBe(22_000_000)
        ->and($setter->fresh()->status)->toBe(SetareganPriceSetter::STATUS_OUT_OF_STOCK)
        ->and($setter->fresh()->last_stock_action)->toBe('zeroed')
        ->and($setter->fresh()->last_supplier_available)->toBeFalse();

    Queue::assertPushed(SendBaleMessageJob::class);
});

test('setaregan pricing rule restores default quantity when supplier is back in stock', function () {
    ['price' => $price, 'setter' => $setter] = createSetareganPricingRule([
        'default_quantity' => 3,
        'status' => SetareganPriceSetter::STATUS_OUT_OF_STOCK,
        'last_supplier_available' => false,
        'last_stock_action' => 'zeroed',
    ], [
        'quantity' => 0,
    ]);
    fakeSetareganPage(setareganInStockHtml(20_000_000));

    SetareganPriceSetterJob::dispatchSync($setter);

    expect((float) $price->fresh()->quantity)->toBe(3.0)
        ->and((int) $price->fresh()->price)->toBe(19_500_000)
        ->and((int) $price->fresh()->sale_price)->toBe(19_500_000)
        ->and($setter->fresh()->last_stock_action)->toBe('restored')
        ->and($setter->fresh()->status)->toBe(SetareganPriceSetter::STATUS_UPDATED);
});

test('setaregan pricing rule does not restore stock zeroed by a sale', function () {
    ['price' => $price, 'setter' => $setter] = createSetareganPricingRule([
        'default_quantity' => 3,
        'status' => SetareganPriceSetter::STATUS_UPDATED,
        'last_supplier_available' => true,
    ], [
        'quantity' => 0,
        'price' => 19_500_000,
        'sale_price' => 19_500_000,
    ]);
    fakeSetareganPage(setareganInStockHtml(20_000_000));

    SetareganPriceSetterJob::dispatchSync($setter);

    expect((float) $price->fresh()->quantity)->toBe(0.0)
        ->and($setter->fresh()->last_stock_action)->toBeNull()
        ->and($setter->fresh()->status)->toBe(SetareganPriceSetter::STATUS_UNCHANGED);
});

test('setaregan pricing rule does not overwrite positive stock on restock cycle', function () {
    ['price' => $price, 'setter' => $setter] = createSetareganPricingRule([], [
        'quantity' => 5,
        'price' => 19_500_000,
        'sale_price' => 19_500_000,
    ]);
    fakeSetareganPage(setareganInStockHtml(20_000_000));

    SetareganPriceSetterJob::dispatchSync($setter);

    expect((float) $price->fresh()->quantity)->toBe(5.0)
        ->and($setter->fresh()->status)->toBe(SetareganPriceSetter::STATUS_UNCHANGED)
        ->and($setter->fresh()->last_stock_action)->toBeNull();
});

test('setaregan fetch failure does not zero stock or change price', function () {
    ['price' => $price, 'setter' => $setter] = createSetareganPricingRule();
    fakeSetareganPage('', 500);

    expect(fn () => SetareganPriceSetterJob::dispatchSync($setter))->toThrow(SetareganFetchException::class)
        ->and((float) $price->fresh()->quantity)->toBe(5.0)
        ->and((int) $price->fresh()->price)->toBe(22_000_000)
        ->and($setter->fresh()->status)->toBe(SetareganPriceSetter::STATUS_FETCH_FAILED);
});

test('setaregan page not found does not zero stock and does not throw', function () {
    Queue::fake([SendBaleMessageJob::class]);
    enableSetareganBaleNotifications();

    ['price' => $price, 'setter' => $setter] = createSetareganPricingRule();
    fakeSetareganPage('', 404);

    SetareganPriceSetterJob::dispatchSync($setter);

    expect((float) $price->fresh()->quantity)->toBe(5.0)
        ->and((int) $price->fresh()->price)->toBe(22_000_000)
        ->and($setter->fresh()->status)->toBe(SetareganPriceSetter::STATUS_PAGE_NOT_FOUND);

    Queue::assertPushed(SendBaleMessageJob::class);
});

test('setaregan in-stock page without price writes nothing', function () {
    ['price' => $price, 'setter' => $setter] = createSetareganPricingRule([], [
        'quantity' => 0,
    ]);
    fakeSetareganPage('<html><body><h1>محصول</h1><button>افزودن به سبد خرید</button></body></html>');

    SetareganPriceSetterJob::dispatchSync($setter);

    expect((float) $price->fresh()->quantity)->toBe(0.0)
        ->and((int) $price->fresh()->price)->toBe(22_000_000)
        ->and($setter->fresh()->status)->toBe(SetareganPriceSetter::STATUS_NO_PRICE);
});

test('invalid Setaregan URLs are rejected before making an outbound request', function () {
    ['price' => $price, 'setter' => $setter] = createSetareganPricingRule();
    $setter->priceFetcher->update(['url' => 'https://example.com/product/sample']);
    Http::fake();

    expect(fn () => SetareganPriceSetterJob::dispatchSync($setter))->toThrow(RuntimeException::class)
        ->and((int) $price->fresh()->price)->toBe(22_000_000);

    Http::assertNothingSent();
});

test('setaregan availability ignores ناموجود in related products section', function () {
    Cache::flush();
    fakeSetareganPage(setareganInStockHtml(19_000_000));

    $offer = App\Support\SetareganPriceFetcher::fetchOffer('https://setaregan.co/product/related-noise');

    expect($offer['available'])->toBeTrue()
        ->and($offer['price'])->toBe(19_000_000);
});

test('shop users can create a Setaregan pricing rule from the price fetcher panel', function () {
    ['product' => $product, 'price' => $price, 'fetcher' => $oldFetcher] = createSetareganPricingRule();
    $oldFetcher->delete();

    Gate::define('shop_access', fn (): bool => true);
    $this->actingAs(User::create([
        'first_name' => 'Setaregan',
        'last_name' => 'Manager',
        'mobile' => '0912'.random_int(1000000, 9999999),
    ]));

    Livewire::test(PriceFetchers::class)
        ->call('assignData', $product->id)
        ->set('type', 'setaregan')
        ->set('setareganAutoPricing', true)
        ->set('url', 'https://setaregan.co/product/sample-panel')
        ->set('productPriceId', $price->id)
        ->set('marginAmount', '۵۰۰٬۰۰۰')
        ->set('minPrice', '18,000,000')
        ->set('maxPrice', '24,000,000')
        ->set('defaultQuantity', '2')
        ->call('addPriceFetcher')
        ->assertHasNoErrors();

    $fetcher = PriceFetcher::query()->where('product_id', $product->id)->sole();
    $setter = $fetcher->setareganPriceSetter;

    expect($fetcher->type)->toBe('setaregan')
        ->and($setter)->not->toBeNull()
        ->and($setter->margin_amount)->toBe(500_000)
        ->and($setter->default_quantity)->toBe(2);
});

test('shop users can create a plain Setaregan fetcher without auto pricing', function () {
    ['product' => $product, 'fetcher' => $oldFetcher] = createSetareganPricingRule();
    $oldFetcher->delete();

    Gate::define('shop_access', fn (): bool => true);
    $this->actingAs(User::create([
        'first_name' => 'Setaregan',
        'last_name' => 'Plain',
        'mobile' => '0912'.random_int(1000000, 9999999),
    ]));

    Livewire::test(PriceFetchers::class)
        ->call('assignData', $product->id)
        ->set('type', 'setaregan')
        ->set('setareganAutoPricing', false)
        ->set('url', 'https://setaregan.co/product/monitor-only')
        ->call('addPriceFetcher')
        ->assertHasNoErrors();

    $fetcher = PriceFetcher::query()->where('product_id', $product->id)->sole();

    expect($fetcher->type)->toBe('setaregan')
        ->and($fetcher->setareganPriceSetter)->toBeNull();
});

test('setaregan pricing rejects a variant already used by a Torob rule', function () {
    ['product' => $product, 'price' => $price] = createSetareganPricingRule();
    PriceFetcher::query()->where('product_id', $product->id)->delete();

    $torobFetcher = PriceFetcher::create([
        'product_id' => $product->id,
        'type' => 'torob',
        'url' => 'https://torob.com/p/ad77d6f4-d0de-4ec9-9572-a05fbd27ad70/sample/',
    ]);
    TorobPriceSetter::create([
        'price_fetcher_id' => $torobFetcher->id,
        'product_price_id' => $price->id,
        'own_shop_names' => ['هجده شصت'],
        'step_amount' => 10_000,
        'min_price' => 18_000_000,
        'max_price' => 24_000_000,
        'is_active' => true,
    ]);

    Gate::define('shop_access', fn (): bool => true);
    $this->actingAs(User::create([
        'first_name' => 'Setaregan',
        'last_name' => 'Unique',
        'mobile' => '0912'.random_int(1000000, 9999999),
    ]));

    Livewire::test(PriceFetchers::class)
        ->call('assignData', $product->id)
        ->set('type', 'setaregan')
        ->set('setareganAutoPricing', true)
        ->set('url', 'https://setaregan.co/product/conflict')
        ->set('productPriceId', $price->id)
        ->set('marginAmount', '500000')
        ->set('minPrice', '18000000')
        ->set('maxPrice', '24000000')
        ->set('defaultQuantity', '1')
        ->call('addPriceFetcher')
        ->assertHasErrors(['productPriceId']);
});

test('Setaregan sync command dispatches only active pricing rules', function () {
    Queue::fake();
    ['setter' => $activeSetter] = createSetareganPricingRule();
    ['setter' => $inactiveSetter] = createSetareganPricingRule(['is_active' => false]);

    $this->artisan('shop:sync-setaregan-prices')->assertSuccessful();

    Queue::assertPushed(SetareganPriceSetterJob::class, 1);
    Queue::assertPushed(
        SetareganPriceSetterJob::class,
        fn (SetareganPriceSetterJob $job): bool => $job->priceSetter->is($activeSetter)
    );
    Queue::assertNotPushed(
        SetareganPriceSetterJob::class,
        fn (SetareganPriceSetterJob $job): bool => $job->priceSetter->is($inactiveSetter)
    );
});

test('Setaregan sync command dispatches least recently checked rules first', function () {
    Queue::fake();
    ['setter' => $recentSetter] = createSetareganPricingRule([
        'last_checked_at' => now()->subMinutes(5),
    ]);
    ['setter' => $neverCheckedSetter] = createSetareganPricingRule([
        'last_checked_at' => null,
    ]);
    ['setter' => $oldSetter] = createSetareganPricingRule([
        'last_checked_at' => now()->subDay(),
    ]);

    $this->artisan('shop:sync-setaregan-prices')->assertSuccessful();

    $dispatchedIds = Queue::pushed(SetareganPriceSetterJob::class)
        ->map(fn (SetareganPriceSetterJob $job): int => $job->priceSetter->id)
        ->all();

    expect($dispatchedIds)->toBe([
        $neverCheckedSetter->id,
        $oldSetter->id,
        $recentSetter->id,
    ]);
});
