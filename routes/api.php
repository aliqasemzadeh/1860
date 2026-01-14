<?php

use Illuminate\Support\Facades\Route;

Route::post('/sepidar/invoices', [\App\Http\Controllers\SepidarController::class, 'invoices'])->name('sepidar.invoices');

Route::post('/sepidar/banks', [\App\Http\Controllers\SepidarController::class, 'banks'])->name('sepidar.banks');
Route::post('/sepidar/bank-accounts', [\App\Http\Controllers\SepidarController::class, 'bank_accounts'])->name('sepidar.bank-accounts');
Route::post('/sepidar/bank-account-balances', [\App\Http\Controllers\SepidarController::class, 'bank_account_balances'])->name('sepidar.bank-account-balances');

Route::post('/sepidar/items', [\App\Http\Controllers\SepidarController::class, 'items'])->name('sepidar.items');
Route::post('/sepidar/item-stock-summaries', [\App\Http\Controllers\SepidarController::class, 'item_stock_summaries'])->name('sepidar.item-stock-summaries');

Route::post('/sepidar/grouping', [\App\Http\Controllers\SepidarController::class, 'grouping'])->name('sepidar.grouping');
