<?php

use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\Main\Dashboard\Index::class)->name('home');

Route::get('/category/{id}/{slug?}', \App\Livewire\Main\Category\View::class)
    ->whereNumber('id')
    ->name('category.view');
Route::get('/product/{id}/{slug?}', \App\Livewire\Main\Product\View::class)
    ->whereNumber('id')
    ->name('product.view');

Route::get('/blog', \App\Livewire\Main\Content\Post\Index::class)
    ->name('post.index');

Route::get('/box/{id}/{slug?}', \App\Livewire\Main\Content\Box\View::class)
    ->whereNumber('id')
    ->name('content.box.view');

Route::get('/post/{slug}', \App\Livewire\Main\Content\Post\View::class)
    ->name('post.view');

Route::get('/tag/{id}/{slug?}', \App\Livewire\Main\Content\Tag\View::class)
    ->whereNumber('id')
    ->name('tag.view');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

// Legacy redirects (301) for old product/category URLs
Route::get('/category/id/{id}', function (int $id) {
    $category = \App\Models\Shop\Category::query()->findOrFail($id);

    return redirect()->to($category->url, 301);
})->whereNumber('id')->name('category.view.id');

Route::get('/product/id/{id}', function (int $id) {
    $product = \App\Models\Shop\Product::query()->findOrFail($id);

    return redirect()->to($product->url, 301);
})->whereNumber('id')->name('product.view.id');

Route::get('/category/{slug}', function (string $slug) {
    $category = \App\Models\Shop\Category::query()
        ->where(function ($q) use ($slug) {
            $q->where('slug', $slug)->orWhere('slug_fa', $slug);
        })
        ->firstOrFail();

    return redirect()->to($category->url, 301);
})->where('slug', '^(?!id$)[^/]+$')->name('category.view.legacy');

Route::get('/product/{slug}', function (string $slug) {
    $product = \App\Models\Shop\Product::query()
        ->where(function ($q) use ($slug) {
            $q->where('slug', $slug)->orWhere('slug_fa', $slug);
        })
        ->firstOrFail();

    return redirect()->to($product->url, 301);
})->where('slug', '^(?!id$)[^/]+$')->name('product.view.legacy');

Route::get('/contact', \App\Livewire\Main\Contact\Index::class)->name('contact.index');

Route::group(['middleware' => ['auth']], function () {

    Route::get('/cart', \App\Livewire\Main\Order\Cart::class)->name('order.cart');
    Route::get('/checkout', \App\Livewire\Main\Order\Checkout::class)->name('order.checkout');
    Route::get('/shipping', \App\Livewire\Main\Order\Shipping::class)->name('order.shipping');
    Route::get('/orders', \App\Livewire\Main\Order\Index::class)->name('order.index');
    Route::get('/orders/{id}', \App\Livewire\Main\Order\View::class)->name('order.view');
    Route::get('/orders/{id}/payment', \App\Livewire\Main\Order\Payment::class)->name('order.payment');

    Route::get('/panel/user/dashboard/index', \App\Livewire\Panel\User\Dashboard\Index::class)->name('panel.user.dashboard.index');
    Route::get('/panel/user/order/index', \App\Livewire\Panel\User\Order\Index::class)->name('panel.user.order.index');
    Route::get('/panel/user/address/index', \App\Livewire\Panel\User\Address\Index::class)->name('panel.user.address.index');

    Route::get('/panel/administrator/dashboard/index', \App\Livewire\Panel\Administrator\Dashboard\Index::class)->name('panel.administrator.dashboard.index');
    Route::get('/panel/administrator/user-management/user/index', \App\Livewire\Panel\Administrator\UserManagement\User\Index::class)->name('panel.administrator.user-management.user.index');
    Route::get('/panel/administrator/user-management/role/index', \App\Livewire\Panel\Administrator\UserManagement\Role\Index::class)->name('panel.administrator.user-management.role.index');
    Route::get('/panel/administrator/user-management/permission/index', \App\Livewire\Panel\Administrator\UserManagement\Permission\Index::class)->name('panel.administrator.user-management.permission.index');

    Route::get('/panel/content/box/index', \App\Livewire\Panel\Content\Box\Index::class)->name('panel.content.box.index');

    Route::get('/panel/administrator/setting-management/function/index', \App\Livewire\Panel\Administrator\SettingManagement\Function\Index::class)->name('panel.administrator.setting-management.function.index');
    Route::get('/panel/administrator/setting-management/backup/index', \App\Livewire\Panel\Administrator\SettingManagement\Backup\Index::class)->name('panel.administrator.setting-management.backup.index');
    Route::get('/panel/administrator/setting-management/option/index', \App\Livewire\Panel\Administrator\SettingManagement\Option\Index::class)->name('panel.administrator.setting-management.option.index');

    Route::get('/panel/shop/dashboard/index', \App\Livewire\Panel\Shop\Dashboard\Index::class)->name('panel.shop.dashboard.index');

    Route::get('/panel/shop/product/index', \App\Livewire\Panel\Shop\Product\Index::class)->name('panel.shop.product.index');
    Route::get('/panel/shop/product/pricing/{productId}', \App\Livewire\Panel\Shop\Product\Pricing\Index::class)->name('panel.shop.product.pricing.index');
    Route::get('/panel/shop/product/attributes/{id}', \App\Livewire\Panel\Shop\Product\Attributes::class)->name('panel.shop.product.attributes.index');
    Route::get('/panel/shop/order/index', \App\Livewire\Panel\Shop\Order\Index::class)->name('panel.shop.order.index');

    Route::get('/panel/shop/setting-management/category/index', \App\Livewire\Panel\Shop\SettingManagement\Category\Index::class)->name('panel.shop.setting-management.category.index');
    Route::get('/panel/shop/setting-management/category/attributes/{id}', \App\Livewire\Panel\Shop\SettingManagement\Category\Attributes::class)->name('panel.shop.setting-management.category.attributes');
    Route::get('/panel/shop/setting-management/brand/index', \App\Livewire\Panel\Shop\SettingManagement\Brand\Index::class)->name('panel.shop.setting-management.brand.index');
    Route::get('/panel/shop/setting-management/color/index', \App\Livewire\Panel\Shop\SettingManagement\Color\Index::class)->name('panel.shop.setting-management.color.index');
    Route::get('/panel/shop/setting-management/warranty/index', \App\Livewire\Panel\Shop\SettingManagement\Warranty\Index::class)->name('panel.shop.setting-management.warranty.index');
    Route::get('/panel/shop/setting-management/unit/index', \App\Livewire\Panel\Shop\SettingManagement\Unit\Index::class)->name('panel.shop.setting-management.unit.index');

    // Shop / Attribute Groups management
    Route::get('/panel/shop/setting-management/attribute-group/index', \App\Livewire\Panel\Shop\SettingManagement\AttributeGroup\Index::class)->name('panel.shop.setting-management.attribute-group.index');

    // Shop / Attributes management
    Route::get('/panel/shop/setting-management/attribute/index', \App\Livewire\Panel\Shop\SettingManagement\Attribute\Index::class)->name('panel.shop.setting-management.attribute.index');
    Route::get('/panel/shop/setting-management/attribute/options/{attributeId}', \App\Livewire\Panel\Shop\SettingManagement\Attribute\Option\Index::class)->name('panel.shop.setting-management.attribute.options.index');

    // Shop / Shipping management
    Route::get('/panel/shop/shipping/method/index', \App\Livewire\Panel\Shop\Shipping\Method\Index::class)->name('panel.shop.shipping.method.index');
    Route::get('/panel/shop/shipping/zone/index', \App\Livewire\Panel\Shop\Shipping\Zone\Index::class)->name('panel.shop.shipping.zone.index');
    Route::get('/panel/shop/shipping/rate/index', \App\Livewire\Panel\Shop\Shipping\Rate\Index::class)->name('panel.shop.shipping.rate.index');

    Route::get('/panel/content/post/index', \App\Livewire\Panel\Content\Post\Index::class)
        ->name('panel.content.post.index');

});

Route::get('/login', \App\Livewire\Auth\Login::class)->name('login')->middleware('guest');
Route::get('/logout', \App\Livewire\Auth\Logout::class)->name('logout')->middleware('auth');
