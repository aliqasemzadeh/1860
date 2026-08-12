<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\Main\Dashboard\Index::class)->name('home');
Route::get('/category/{slug}', \App\Livewire\Main\Category\View::class)->name('category.view');
Route::get('/category/id/{id}', \App\Livewire\Main\Category\View::class)->name('category.view.id');
Route::get('/product/{slug}', \App\Livewire\Main\Product\View::class)->name('product.view');
Route::get('/product/id/{id}', \App\Livewire\Main\Product\View::class)->name('product.view.id');
Route::get('/contact', \App\Livewire\Main\Contact\Index::class)->name('contact.index');

Route::group(['middleware' => ['auth']], function () {

    Route::get('/cart', \App\Livewire\Main\Order\Cart::class)->name('order.cart');
    Route::get('/checkout', \App\Livewire\Main\Order\Checkout::class)->name('order.checkout');
    Route::get('/shipping', \App\Livewire\Main\Order\Shipping::class)->name('order.shipping');
    Route::get('/orders', \App\Livewire\Main\Order\Index::class)->name('order.index');
    Route::get('/orders/{id}', \App\Livewire\Main\Order\View::class)->name('order.view');
    Route::get('/orders/{id}/payment', \App\Livewire\Main\Order\Payment::class)->name('order.payment');


    Route::get('/panel/user/dashboard/index', \App\Livewire\Panel\User\Dashboard\Index::class)->name('panel.user.dashboard.index');

    Route::get('/panel/administrator/dashboard/index', \App\Livewire\Panel\Administrator\Dashboard\Index::class)->name('panel.administrator.dashboard.index');
    Route::get('/panel/administrator/user-management/user/index', \App\Livewire\Panel\Administrator\UserManagement\User\Index::class)->name('panel.administrator.user-management.user.index');
    Route::get('/panel/administrator/user-management/role/index', \App\Livewire\Panel\Administrator\UserManagement\Role\Index::class)->name('panel.administrator.user-management.role.index');
    Route::get('/panel/administrator/user-management/permission/index', \App\Livewire\Panel\Administrator\UserManagement\Permission\Index::class)->name('panel.administrator.user-management.permission.index');

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

});

Route::get('/login', \App\Livewire\Auth\Login::class)->name('login')->middleware('guest');
Route::get('/logout', \App\Livewire\Auth\Logout::class)->name('logout')->middleware('auth');

