<?php

use App\Enums\OrderStatusEnum;
use App\Models\Shop\Brand;
use App\Models\Shop\Category;
use App\Models\Shop\Order;
use App\Models\Shop\OrderItem;
use App\Models\Shop\Product;
use App\Models\Shop\ProductPrice;
use App\Models\Shop\Unit;
use App\Models\User;
use App\Services\Shop\OrderStatusService;
use Illuminate\Support\Facades\Queue;

function createInventoryProduct(array $priceOverrides = []): array
{
    $suffix = uniqid();
    $category = Category::create([
        'name' => 'Inventory category '.$suffix,
        'slug' => 'inventory-category-'.$suffix,
        'slug_fa' => 'inventory-category-fa-'.$suffix,
    ]);
    $brand = Brand::create([
        'name' => 'Inventory brand '.$suffix,
        'slug' => 'inventory-brand-'.$suffix,
        'slug_fa' => 'inventory-brand-fa-'.$suffix,
    ]);
    $unit = Unit::create(['name' => 'عدد '.$suffix]);
    $product = Product::create([
        'name' => 'Inventory product '.$suffix,
        'slug' => 'inventory-product-'.$suffix,
        'slug_fa' => 'inventory-product-fa-'.$suffix,
        'file_path' => 'products/inventory-test.jpg',
        'file_name' => 'inventory-test.jpg',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'unit_id' => $unit->id,
    ]);
    $price = ProductPrice::create(array_merge([
        'product_id' => $product->id,
        'price' => 1_000_000,
        'sale_price' => 900_000,
        'quantity' => 1,
        'is_default' => true,
    ], $priceOverrides));

    return compact('product', 'price');
}

function createUnpaidOrderWithItem(Product $product, ProductPrice $price, array $itemOverrides = []): Order
{
    $user = User::create([
        'mobile' => '0912'.random_int(1000000, 9999999),
    ]);

    $order = Order::create([
        'user_id' => $user->id,
        'order_number' => Order::generateOrderNumber(),
        'status' => OrderStatusEnum::Pending->value,
        'subtotal_amount' => 900_000,
        'total_amount' => 900_000,
    ]);

    OrderItem::create(array_merge([
        'order_id' => $order->id,
        'sku' => (string) $product->id,
        'name' => $product->name,
        'quantity' => 1,
        'unit_price_amount' => 900_000,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'total_amount' => 900_000,
        'meta' => ['price_id' => $price->id],
    ], $itemOverrides));

    return $order->fresh('items');
}

beforeEach(function () {
    Queue::fake();
});

test('marking an order as paid zeroes product price quantity', function () {
    ['product' => $product, 'price' => $price] = createInventoryProduct(['quantity' => 1]);
    $order = createUnpaidOrderWithItem($product, $price);

    $marked = app(OrderStatusService::class)->markAsPaid($order);

    expect($marked)->toBeTrue()
        ->and((float) $price->fresh()->quantity)->toBe(0.0)
        ->and($order->fresh()->status)->toBe(OrderStatusEnum::Processing->value)
        ->and($order->fresh()->paid_at)->not->toBeNull();
});

test('marking an order as paid deducts inventory using string meta price_id', function () {
    ['product' => $product, 'price' => $price] = createInventoryProduct(['quantity' => 2]);
    $order = createUnpaidOrderWithItem($product, $price, [
        'quantity' => 2,
        'total_amount' => 1_800_000,
        // Stored as a JSON string (legacy / double-encoded style); array cast returns a string.
        'meta' => json_encode(['price_id' => $price->id]),
    ]);

    app(OrderStatusService::class)->markAsPaid($order->fresh('items'));

    expect((float) $price->fresh()->quantity)->toBe(0.0);
});

test('marking an order as paid falls back to sku product id when meta has no price_id', function () {
    ['product' => $product, 'price' => $price] = createInventoryProduct(['quantity' => 3]);
    $order = createUnpaidOrderWithItem($product, $price, [
        'quantity' => 1,
        'meta' => [],
    ]);

    app(OrderStatusService::class)->markAsPaid($order);

    expect((float) $price->fresh()->quantity)->toBe(2.0);
});

test('marking an already paid order does not deduct inventory twice', function () {
    ['product' => $product, 'price' => $price] = createInventoryProduct(['quantity' => 1]);
    $order = createUnpaidOrderWithItem($product, $price);

    $service = app(OrderStatusService::class);
    expect($service->markAsPaid($order))->toBeTrue()
        ->and((float) $price->fresh()->quantity)->toBe(0.0)
        ->and($service->markAsPaid($order->fresh()))->toBeFalse()
        ->and((float) $price->fresh()->quantity)->toBe(0.0);
});
