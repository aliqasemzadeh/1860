<?php

use Illuminate\Support\Facades\Route;

Route::post('/torob_api/v3/products', \App\Http\Controllers\TorobProductFeedController::class)
    ->middleware(\App\Http\Middleware\ValidateTorobToken::class)
    ->name('torob.products.v3');

// Payment callback (no auth required, handled by payment gateway)
Route::match(['get', 'post'], '/payment/callback/{orderId}', [\App\Http\Controllers\PaymentController::class, 'callback'])->name('payment.callback');
