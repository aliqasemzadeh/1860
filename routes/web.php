<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\Main\Dashboard\Index::class)->name('home');
Route::get('/product/{slug}', \App\Livewire\Main\Product\View::class)->name('product.view');
Route::get('/product/id/{id}', \App\Livewire\Main\Product\View::class)->name('product.view.id');

Route::group(['middleware' => ['auth']], function () {

    Route::get('/panel/service-center/dashboard/index', \App\Livewire\Panel\ServiceCenter\Dashboard\Index::class)->name('panel.service-center.dashboard');

    Route::get('/panel/service-center/dashboard/index', \App\Livewire\Panel\ServiceCenter\Dashboard\Index::class)->name('panel.service-center.dashboard.index');
    Route::get('/panel/service-center/assembly/index', \App\Livewire\Panel\ServiceCenter\Assembly\Index::class)->name('panel.service-center.assembly.index');
    Route::get('/panel/service-center/repair/index', \App\Livewire\Panel\ServiceCenter\Repair\Index::class)->name('panel.service-center.repair.index');

    Route::get('/panel/crm/dashboard/index', \App\Livewire\Panel\Crm\Dashboard\Index::class)->name('panel.crm.dashboard.index');

    Route::get('/administrator/dashboard/index', \App\Livewire\Panel\Administrator\Dashboard\Index::class)->name('panel.administrator.dashboard.index');
    Route::get('/administrator/user-management/user/index', \App\Livewire\Panel\Administrator\UserManagement\User\Index::class)->name('panel.administrator.user-management.user.index');
    Route::get('/administrator/user-management/role/index', \App\Livewire\Panel\Administrator\UserManagement\Role\Index::class)->name('panel.administrator.user-management.role.index');
    Route::get('/administrator/user-management/permission/index', \App\Livewire\Panel\Administrator\UserManagement\Permission\Index::class)->name('panel.administrator.user-management.permission.index');

    Route::get('/administrator/setting-management/function/index', \App\Livewire\Panel\Administrator\SettingManagement\Function\Index::class)->name('panel.administrator.setting-management.function.index');
    Route::get('/administrator/setting-management/option/index', \App\Livewire\Panel\Administrator\SettingManagement\Option\Index::class)->name('panel.administrator.setting-management.option.index');

    Route::get('/panel/shop/dashboard/index', \App\Livewire\Panel\Shop\Dashboard\Index::class)->name('panel.shop.dashboard.index');

    Route::get('/panel/shop/product/index', \App\Livewire\Panel\Shop\Product\Index::class)->name('panel.shop.product.index');
    Route::get('/panel/shop/product/pricing/{productId}', \App\Livewire\Panel\Shop\Product\Pricing\Index::class)->name('panel.shop.product.pricing.index');
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
    Route::get('/panel/shop/setting-management/attribute/options/{id}', \App\Livewire\Panel\Shop\SettingManagement\Attribute\Option\Index::class)->name('panel.shop.setting-management.attribute.options.index');

    // Shop / Shipping management
    Route::get('/panel/shop/shipping/method/index', \App\Livewire\Panel\Shop\Shipping\Method\Index::class)->name('panel.shop.shipping.method.index');
    Route::get('/panel/shop/shipping/zone/index', \App\Livewire\Panel\Shop\Shipping\Zone\Index::class)->name('panel.shop.shipping.zone.index');
    Route::get('/panel/shop/shipping/rate/index', \App\Livewire\Panel\Shop\Shipping\Rate\Index::class)->name('panel.shop.shipping.rate.index');


    Route::get('/accounting/dashboard/index', \App\Livewire\Panel\Accounting\Dashboard\Index::class)->name('accounting.dashboard.index');
    Route::get('/accounting/bank/index', \App\Livewire\Panel\Accounting\Bank\Index::class)->name('accounting.bank.index');
    Route::get('/accounting/bank/remittance/index', \App\Livewire\Panel\Accounting\Bank\Remittance\Index::class)->name('accounting.bank.remittance.index');
    Route::get('/accounting/bank/transaction/index', \App\Livewire\Panel\Accounting\Bank\Transaction\Index::class)->name('accounting.bank.transaction.index');
    Route::get('/accounting/remittance/index', \App\Livewire\Panel\Accounting\Remittance\Index::class)->name('accounting.remittance.index');
    Route::get('/accounting/cheque/index', \App\Livewire\Panel\Accounting\Cheque\Index::class)->name('accounting.cheque.index');

});

Route::get('/login', \App\Livewire\Auth\Login::class)->name('login')->middleware('guest');
Route::get('/logout', \App\Livewire\Auth\Logout::class)->name('logout')->middleware('auth');
