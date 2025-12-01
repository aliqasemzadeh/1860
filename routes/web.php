<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('home');
});

Route::get('/login', \App\Livewire\Auth\Login::class)->name('login');
