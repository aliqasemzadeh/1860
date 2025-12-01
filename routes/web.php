<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', \App\Livewire\Main\Dashboard\Index::class)->name('home');
    Route::get('/service-center/dashboard/index', \App\Livewire\ServiceCenter\Dashboard\Index::class)->name('dashboard');
});

Route::get('/login', \App\Livewire\Auth\Login::class)->name('login')->middleware('guest');
