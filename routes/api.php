<?php

use Illuminate\Support\Facades\Route;

Route::get('/spidar/invoices', [\App\Http\Controllers\SpidarController::class, 'spidar.invoices'])->name('spidar.invoices');
Route::get('/spidar/bank-accounts', [\App\Http\Controllers\SpidarController::class, 'invoices'])->name('spidar.bank-accounts');
