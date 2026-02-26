<?php

use Illuminate\Support\Facades\Route;

// Payment callback (no auth required, handled by payment gateway)
Route::post('/payment/callback', [\App\Http\Controllers\PaymentController::class, 'callback'])->name('payment.callback');
