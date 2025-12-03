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
    Route::get('/shop/category/index', \App\Livewire\Shop\Category\Index::class)->name('shop.category.index');
    Route::get('/shop/brand/index', \App\Livewire\Shop\Brand\Index::class)->name('shop.brand.index');
    Route::get('/shop/color/index', \App\Livewire\Shop\Color\Index::class)->name('shop.color.index');
    Route::get('/shop/warranty/index', \App\Livewire\Shop\Warranty\Index::class)->name('shop.warranty.index');
    Route::get('/shop/product/index', \App\Livewire\Shop\Product\Index::class)->name('shop.product.index');
    Route::get('/shop/order/index', \App\Livewire\Shop\Order\Index::class)->name('shop.order.index');

});

Route::get('/login', \App\Livewire\Auth\Login::class)->name('login')->middleware('guest');
Route::get('/logout', \App\Livewire\Auth\Logout::class)->name('logout')->middleware('auth');
