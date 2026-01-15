<?php

use Illuminate\Support\Facades\Route;

Route::post('/sepidar/invoices', [\App\Http\Controllers\SepidarController::class, 'invoices'])->name('sepidar.invoices');
Route::post('/sepidar/invoice-items', [\App\Http\Controllers\SepidarController::class, 'invoice_items'])->name('sepidar.invoice-items');

Route::post('/sepidar/banks', [\App\Http\Controllers\SepidarController::class, 'banks'])->name('sepidar.banks');
Route::post('/sepidar/bank-accounts', [\App\Http\Controllers\SepidarController::class, 'bank_accounts'])->name('sepidar.bank-accounts');
Route::post('/sepidar/bank-account-balances', [\App\Http\Controllers\SepidarController::class, 'bank_account_balances'])->name('sepidar.bank-account-balances');

Route::post('/sepidar/items', [\App\Http\Controllers\SepidarController::class, 'items'])->name('sepidar.items');
Route::post('/sepidar/item-stock-summaries', [\App\Http\Controllers\SepidarController::class, 'item_stock_summaries'])->name('sepidar.item-stock-summaries');

Route::post('/sepidar/inventory-receipts', [\App\Http\Controllers\SepidarController::class, 'inventory_receipts'])->name('sepidar.inventory-receipts');
Route::post('/sepidar/inventory-receipt-items', [\App\Http\Controllers\SepidarController::class, 'inventory_receipt_items'])->name('sepidar.inventory-receipt-items');

Route::post('/sepidar/grouping', [\App\Http\Controllers\SepidarController::class, 'grouping'])->name('sepidar.grouping');

Route::post('/sepidar/dls', [\App\Http\Controllers\SepidarController::class, 'dls'])->name('sepidar.dls');
