<?php

use App\Jobs\Shop\TorobPriceSetterJob;
use App\Models\Shop\Brand;
use App\Models\Shop\Category;
use App\Models\Shop\PriceFetcher;
use App\Models\Shop\Product;
use App\Models\Shop\ProductPrice;
use App\Models\Shop\TorobPriceSetter;
use App\Models\Shop\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

function createTorobPricingRule(array $setterOverrides = [], array $priceOverrides = []): array
{
    $suffix = uniqid();
    $category = Category::create([
        'name' => 'Torob category '.$suffix,
        'slug' => 'torob-category-'.$suffix,
        'slug_fa' => 'torob-category-fa-'.$suffix,
    ]);
    $brand = Brand::create([
        'name' => 'Torob brand '.$suffix,
        'slug' => 'torob-brand-'.$suffix,
        'slug_fa' => 'torob-brand-fa-'.$suffix,
    ]);
    $unit = Unit::create(['name' => 'عدد '.$suffix]);
    $product = Product::create([
        'name' => 'Torob product '.$suffix,
        'slug' => 'torob-product-'.$suffix,
        'slug_fa' => 'torob-product-fa-'.$suffix,
        'file_path' => 'products/torob-test.jpg',
        'file_name' => 'torob-test.jpg',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'unit_id' => $unit->id,
    ]);
    $price = ProductPrice::create(array_merge([
        'product_id' => $product->id,
        'price' => 22_000_000,
        'quantity' => 5,
        'is_default' => true,
    ], $priceOverrides));
    $fetcher = PriceFetcher::create([
        'product_id' => $product->id,
        'type' => 'torob',
        'url' => 'https://torob.com/p/ad77d6f4-d0de-4ec9-9572-a05fbd27ad70/sample/',
    ]);
    $setter = TorobPriceSetter::create(array_merge([
        'price_fetcher_id' => $fetcher->id,
        'product_price_id' => $price->id,
        'own_shop_names' => ['هجده شصت'],
        'step_amount' => 10_000,
        'min_price' => 18_000_000,
        'max_price' => 24_000_000,
        'is_active' => true,
    ], $setterOverrides));

    return compact('product', 'price', 'fetcher', 'setter');
}

function fakeTorobOffers(array $offers): void
{
    Http::fake([
        'api.torob.com/*' => Http::response([
            'count' => count($offers),
            'results' => $offers,
        ]),
    ]);
}

beforeEach(fn () => Cache::flush());

test('Torob panel formats prices with Persian digits', function () {
    app()->setLocale('fa');

    expect((new \App\Livewire\Panel\Shop\Product\PriceFetchers)->formatNumber(1_234_567))
        ->toBe('۱,۲۳۴,۵۶۷');
});

test('price fetchers accept long percent-encoded Torob URLs', function () {
    expect(Schema::getColumnType('price_fetchers', 'url'))->toBe('text');

    ['fetcher' => $fetcher] = createTorobPricingRule();
    $longUrl = 'https://torob.com/p/ad77d6f4-d0de-4ec9-9572-a05fbd27ad70/'.str_repeat('%D9%BE%D8%B1%DB%8C%D9%86%D8%AA%D8%B1-', 20);

    $fetcher->update(['url' => $longUrl]);

    expect($fetcher->fresh()->url)->toBe($longUrl)
        ->and(strlen($longUrl))->toBeGreaterThan(255);
});

test('torob pricing rule undercuts the cheapest eligible competitor and excludes own shop', function () {
    ['price' => $price, 'fetcher' => $fetcher, 'setter' => $setter] = createTorobPricingRule();

    fakeTorobOffers([
        ['availability' => true, 'shop_name' => 'هجده‌شصت', 'price' => 19_500_000, 'prk' => 'self'],
        ['availability' => true, 'shop_name' => 'رقیب معتبر', 'price' => 21_000_000, 'prk' => 'competitor'],
        ['availability' => false, 'shop_name' => 'ناموجود', 'price' => 18_000_000, 'prk' => 'unavailable'],
    ]);

    TorobPriceSetterJob::dispatchSync($setter);

    expect((int) $price->fresh()->price)->toBe(20_990_000)
        ->and($fetcher->fresh()->last_price)->toBe(21_000_000)
        ->and($setter->fresh()->status)->toBe(TorobPriceSetter::STATUS_UPDATED)
        ->and($setter->fresh()->last_competitor_shop)->toBe('رقیب معتبر');
});

test('torob offer fetching follows seller pagination', function () {
    ['price' => $price, 'setter' => $setter] = createTorobPricingRule();
    $ownOffers = array_fill(0, 100, [
        'availability' => true,
        'shop_name' => 'هجده شصت',
        'price' => 19_500_000,
    ]);

    Http::fake([
        'api.torob.com/*' => Http::sequence()
            ->push(['count' => 101, 'results' => $ownOffers])
            ->push(['count' => 101, 'results' => [[
                'availability' => true,
                'shop_name' => 'رقیب صفحه دوم',
                'price' => 21_000_000,
            ]]]),
    ]);

    TorobPriceSetterJob::dispatchSync($setter);

    expect((int) $price->fresh()->price)->toBe(20_990_000)
        ->and($setter->fresh()->last_competitor_shop)->toBe('رقیب صفحه دوم');
    Http::assertSentCount(2);
});

test('torob offer fetching recovers from HTTP 490 with a browser session', function () {
    ['price' => $price, 'setter' => $setter] = createTorobPricingRule();

    Http::fake([
        'api.torob.com/*' => Http::sequence()
            ->push([], 490)
            ->push([
                'count' => 1,
                'results' => [[
                    'availability' => true,
                    'shop_name' => 'رقیب معتبر',
                    'price' => 21_000_000,
                ]],
            ]),
        'torob.com/p/*' => Http::response('', 200),
    ]);

    TorobPriceSetterJob::dispatchSync($setter);

    expect((int) $price->fresh()->price)->toBe(20_990_000);

    Http::assertSent(fn ($request): bool => str_starts_with($request->url(), 'https://api.torob.com/')
        && str_contains($request->url(), 'source=next_desktop')
        && str_starts_with($request->header('User-Agent')[0] ?? '', 'Mozilla/5.0')
        && ($request->header('Referer')[0] ?? '') === 'https://torob.com/');
    Http::assertSent(fn ($request): bool => str_starts_with(
        $request->url(),
        'https://torob.com/p/ad77d6f4-d0de-4ec9-9572-a05fbd27ad70/',
    ));
    Http::assertSentCount(3);
});

test('torob offer fetching falls back to system curl when PHP transport remains blocked', function () {
    ['price' => $price, 'setter' => $setter] = createTorobPricingRule();

    Http::fake([
        'api.torob.com/*' => Http::response([], 490),
        'torob.com/p/*' => Http::response('', 200),
    ]);
    Process::fake([
        '*' => Process::result(json_encode([
            'count' => 1,
            'results' => [[
                'availability' => true,
                'shop_name' => 'رقیب معتبر',
                'price' => 21_000_000,
            ]],
        ], JSON_THROW_ON_ERROR)."\n200"),
    ]);

    TorobPriceSetterJob::dispatchSync($setter);

    expect((int) $price->fresh()->price)->toBe(20_990_000);
    Process::assertRan(function ($process): bool {
        $command = $process->command;

        return is_array($command)
            && in_array('--write-out', $command, true)
            && collect($command)->contains(
                fn (string $argument): bool => str_starts_with($argument, 'https://api.torob.com/v4/base-product/sellers/'),
            );
    });
});

test('torob offer fetching cools down after a final HTTP 490 response', function () {
    ['setter' => $setter] = createTorobPricingRule();

    Http::fake([
        'api.torob.com/*' => Http::response([], 490),
        'torob.com/p/*' => Http::response('', 490),
    ]);
    Process::fake([
        '*' => Process::result("<html>blocked</html>\n490"),
    ]);

    expect(fn () => TorobPriceSetterJob::dispatchSync($setter))
        ->toThrow(RuntimeException::class, 'HTTP 490');

    expect(Cache::has('torob:sellers:blocked-until'))->toBeTrue();

    expect(fn () => TorobPriceSetterJob::dispatchSync($setter->fresh()))
        ->toThrow(RuntimeException::class, 'cooling down');

    Http::assertSentCount(3);
    Process::assertRanTimes(fn (): bool => true, 1);
});

test('torob pricing rule keeps the current price when stop loss is reached', function () {
    ['price' => $price, 'setter' => $setter] = createTorobPricingRule([
        'min_price' => 20_000_000,
    ]);

    fakeTorobOffers([
        ['availability' => true, 'shop_name' => 'رقیب', 'price' => 19_900_000],
    ]);

    TorobPriceSetterJob::dispatchSync($setter);

    expect((int) $price->fresh()->price)->toBe(22_000_000)
        ->and($setter->fresh()->status)->toBe(TorobPriceSetter::STATUS_FLOOR_REACHED)
        ->and($setter->fresh()->last_target_price)->toBe(19_890_000);
});

test('torob pricing rule caps a high target at the configured maximum', function () {
    ['price' => $price, 'setter' => $setter] = createTorobPricingRule();

    fakeTorobOffers([
        ['availability' => true, 'shop_name' => 'رقیب', 'price' => 30_000_000],
    ]);

    TorobPriceSetterJob::dispatchSync($setter);

    expect((int) $price->fresh()->price)->toBe(24_000_000)
        ->and($setter->fresh()->last_target_price)->toBe(24_000_000)
        ->and($setter->fresh()->status)->toBe(TorobPriceSetter::STATUS_UPDATED);
});

test('torob pricing keeps the applied price effective when an old invalid sale price exists', function () {
    ['price' => $price, 'setter' => $setter] = createTorobPricingRule([], [
        'sale_price' => 23_000_000,
    ]);

    fakeTorobOffers([
        ['availability' => true, 'shop_name' => 'رقیب', 'price' => 23_510_000],
    ]);

    TorobPriceSetterJob::dispatchSync($setter);

    expect((int) $price->fresh()->price)->toBe(23_500_000)
        ->and($price->fresh()->sale_price)->toBeNull();
});

test('torob pricing rule fails closed when no eligible competitor exists', function () {
    ['price' => $price, 'fetcher' => $fetcher, 'setter' => $setter] = createTorobPricingRule();

    fakeTorobOffers([
        ['availability' => true, 'shop_name' => 'هجده شصت', 'price' => 19_500_000],
        ['availability' => false, 'shop_name' => 'رقیب ناموجود', 'price' => 18_000_000],
    ]);

    TorobPriceSetterJob::dispatchSync($setter);

    expect((int) $price->fresh()->price)->toBe(22_000_000)
        ->and($fetcher->fresh()->last_price)->toBeNull()
        ->and($setter->fresh()->status)->toBe(TorobPriceSetter::STATUS_NO_COMPETITOR);
});

test('invalid Torob URLs are rejected before making an outbound request', function () {
    ['price' => $price, 'setter' => $setter] = createTorobPricingRule();
    $setter->priceFetcher->update(['url' => 'https://example.com/p/ad77d6f4-d0de-4ec9-9572-a05fbd27ad70/']);
    Http::fake();

    expect(fn () => TorobPriceSetterJob::dispatchSync($setter))->toThrow(RuntimeException::class)
        ->and((int) $price->fresh()->price)->toBe(22_000_000);

    Http::assertNothingSent();
});

test('shop users can create and toggle a Torob pricing rule from the price fetcher panel', function () {
    ['product' => $product, 'price' => $price, 'fetcher' => $oldFetcher] = createTorobPricingRule();
    $oldFetcher->delete();

    Gate::define('shop_access', fn (): bool => true);
    $this->actingAs(User::create([
        'first_name' => 'Torob',
        'last_name' => 'Manager',
        'mobile' => '0912'.random_int(1000000, 9999999),
    ]));

    $component = Livewire::test(\App\Livewire\Panel\Shop\Product\PriceFetchers::class)
        ->call('assignData', $product->id)
        ->set('type', 'torob')
        ->set('url', 'https://torob.com/p/ad77d6f4-d0de-4ec9-9572-a05fbd27ad70/sample/')
        ->set('productPriceId', $price->id)
        ->set('ownShopNames', 'هجده شصت، فروشگاه دوم')
        ->set('stepAmount', '10,000')
        ->set('minPrice', '18,000,000')
        ->set('maxPrice', '24,000,000')
        ->call('addPriceFetcher')
        ->assertHasNoErrors();

    $fetcher = PriceFetcher::query()->where('product_id', $product->id)->sole();
    $setter = $fetcher->torobPriceSetter;

    expect($fetcher->type)->toBe('torob')
        ->and($setter)->not->toBeNull()
        ->and($setter->own_shop_names)->toBe(['هجده شصت', 'فروشگاه دوم'])
        ->and($setter->step_amount)->toBe(10_000);

    $component->call('toggleTorobPriceSetter', $fetcher->id);

    expect($setter->fresh()->is_active)->toBeFalse();
});

test('Torob sync command dispatches only active pricing rules', function () {
    Queue::fake();
    ['setter' => $activeSetter] = createTorobPricingRule();
    ['setter' => $inactiveSetter] = createTorobPricingRule(['is_active' => false]);

    $this->artisan('shop:sync-torob-prices')->assertSuccessful();

    Queue::assertPushed(TorobPriceSetterJob::class, 1);
    Queue::assertPushed(
        TorobPriceSetterJob::class,
        fn (TorobPriceSetterJob $job): bool => $job->priceSetter->is($activeSetter)
    );
    Queue::assertNotPushed(
        TorobPriceSetterJob::class,
        fn (TorobPriceSetterJob $job): bool => $job->priceSetter->is($inactiveSetter)
    );
});
