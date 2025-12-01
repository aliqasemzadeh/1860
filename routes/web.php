<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('/', function () {
        return view('welcome');
    });
});

Route::get('/login', \App\Livewire\Auth\Login::class)->name('login');
