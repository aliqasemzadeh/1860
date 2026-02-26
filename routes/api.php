<?php

use Illuminate\Support\Facades\Route;

// Payment callback (no auth required, handled by payment gateway)
Route::match(['get', 'post'], '/payment/callback/{orderId}', [\App\Http\Controllers\PaymentController::class, 'callback'])->name('payment.callback');
