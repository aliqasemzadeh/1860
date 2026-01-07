<?php

namespace App\Http\Controllers;

use App\Jobs\Spidar\SaveBankAccountDataJob;
use App\Jobs\Spidar\SaveInvoiceDataJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SpidarController extends Controller
{
    public function invoices(Request $request)
    {
        Log::channel('spidar')->info('Invoice POST data received', $request->all());

        SaveInvoiceDataJob::dispatch($request->all());

        return response()->json(['message' => 'Invoice data received and queued for processing']);
    }

    public function bank_accounts(Request $request)
    {
        Log::channel('spidar')->info('Bank Account POST data received', $request->all());

        SaveBankAccountDataJob::dispatch($request->all());

        return response()->json(['message' => 'Bank account data received and queued for processing']);
    }
}
