<?php

use Illuminate\Support\Facades\Route;

Route::post('/sepidar/invoices', [\App\Http\Controllers\SepidarController::class, 'invoices'])->name('sepidar.invoices');
Route::post('/sepidar/bank-accounts', [\App\Http\Controllers\SepidarController::class, 'bank_accounts'])->name('sepidar.bank-accounts');
Route::post('/sepidar/items', [\App\Http\Controllers\SepidarController::class, 'items'])->name('sepidar.items');
