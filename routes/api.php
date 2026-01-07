<?php

use Illuminate\Support\Facades\Route;

Route::post('/spidar/invoices', [\App\Http\Controllers\SpidarController::class, 'invoices'])->name('spidar.invoices');
Route::post('/spidar/bank-accounts', [\App\Http\Controllers\SpidarController::class, 'bank_accounts'])->name('spidar.bank-accounts');
