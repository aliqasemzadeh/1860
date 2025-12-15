<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\Main\Dashboard\Index::class)->name('home');

Route::group(['middleware' => ['auth']], function () {

    Route::get('/service-center/dashboard/index', \App\Livewire\ServiceCenter\Dashboard\Index::class)->name('dashboard');

    Route::get('/service-center/dashboard/index', \App\Livewire\ServiceCenter\Dashboard\Index::class)->name('service-center.dashboard.index');
    Route::get('/service-center/assembly/index', \App\Livewire\ServiceCenter\Assembly\Index::class)->name('service-center.assembly.index');
    Route::get('/service-center/repair/index', \App\Livewire\ServiceCenter\Repair\Index::class)->name('service-center.repair.index');

    Route::get('/crm/dashboard/index', \App\Livewire\Crm\Dashboard\Index::class)->name('crm.dashboard.index');

    Route::get('/administrator/dashboard/index', \App\Livewire\Administrator\Dashboard\Index::class)->name('administrator.dashboard.index');
    Route::get('/administrator/user-management/user/index', \App\Livewire\Administrator\UserManagement\User\Index::class)->name('administrator.user-management.user.index');
    Route::get('/administrator/user-management/role/index', \App\Livewire\Administrator\UserManagement\Role\Index::class)->name('administrator.user-management.role.index');
    Route::get('/administrator/user-management/permission/index', \App\Livewire\Administrator\UserManagement\Permission\Index::class)->name('administrator.user-management.permission.index');

    Route::get('/administrator/setting-management/function/index', \App\Livewire\Administrator\SettingManagement\Function\Index::class)->name('administrator.setting-management.function.index');
    Route::get('/administrator/setting-management/option/index', \App\Livewire\Administrator\SettingManagement\Option\Index::class)->name('administrator.setting-management.option.index');

    Route::get('/shop/dashboard/index', \App\Livewire\Shop\Dashboard\Index::class)->name('shop.dashboard.index');

    Route::get('/shop/product/index', \App\Livewire\Shop\Product\Index::class)->name('shop.product.index');
    Route::get('/shop/product/pricing/{productId}', \App\Livewire\Shop\Product\Pricing\Index::class)->name('shop.product.pricing.index');
    Route::get('/shop/order/index', \App\Livewire\Shop\Order\Index::class)->name('shop.order.index');

    Route::get('/shop/setting-management/category/index', \App\Livewire\Shop\SettingManagement\Category\Index::class)->name('shop.setting-management.category.index');
    Route::get('/shop/setting-management/brand/index', \App\Livewire\Shop\SettingManagement\Brand\Index::class)->name('shop.setting-management.brand.index');
    Route::get('/shop/setting-management/color/index', \App\Livewire\Shop\SettingManagement\Color\Index::class)->name('shop.setting-management.color.index');
    Route::get('/shop/setting-management/warranty/index', \App\Livewire\Shop\SettingManagement\Warranty\Index::class)->name('shop.setting-management.warranty.index');
    Route::get('/shop/setting-management/unit/index', \App\Livewire\Shop\SettingManagement\Unit\Index::class)->name('shop.setting-management.unit.index');

    // Shop / Shipping management
    Route::get('/shop/shipping/method/index', \App\Livewire\Panel\Shop\Shipping\Method\Index::class)->name('shop.shipping.method.index');
    Route::get('/shop/shipping/zone/index', \App\Livewire\Panel\Shop\Shipping\Zone\Index::class)->name('shop.shipping.zone.index');
    Route::get('/shop/shipping/rate/index', \App\Livewire\Panel\Shop\Shipping\Rate\Index::class)->name('shop.shipping.rate.index');


    Route::get('/accounting/dashboard/index', \App\Livewire\Panel\Accounting\Dashboard\Index::class)->name('accounting.dashboard.index');
    Route::get('/accounting/bank/index', \App\Livewire\Panel\Accounting\Bank\Index::class)->name('accounting.bank.index');
    Route::get('/accounting/bank/remittance/index', \App\Livewire\Panel\Accounting\Bank\Remittance\Index::class)->name('accounting.bank.remittance.index');
    Route::get('/accounting/bank/transaction/index', \App\Livewire\Panel\Accounting\Bank\Transaction\Index::class)->name('accounting.bank.transaction.index');

});

Route::get('/login', \App\Livewire\Auth\Login::class)->name('login')->middleware('guest');
Route::get('/logout', \App\Livewire\Auth\Logout::class)->name('logout')->middleware('auth');
