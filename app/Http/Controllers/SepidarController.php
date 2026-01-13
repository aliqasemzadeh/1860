<?php

namespace App\Http\Controllers;

use App\Jobs\Sepidar\SaveBankAccountDataJob;
use App\Jobs\Sepidar\SaveInvoiceDataJob;
use App\Jobs\Sepidar\SaveItemDataJob;
use App\Jobs\Sepidar\SaveGroupingDataJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SepidarController extends Controller
{
    public function invoices(Request $request)
    {
        Log::channel('sepidar')->info('Invoice POST data received', $request->all());

        SaveInvoiceDataJob::dispatch($request->all());

        return response()->json(['message' => 'Invoice data received and queued for processing']);
    }

    public function bank_accounts(Request $request)
    {
        Log::channel('sepidar')->info('Bank Account POST data received', $request->all());

        SaveBankAccountDataJob::dispatch($request->all());

        return response()->json(['message' => 'Bank account data received and queued for processing']);
    }

    public function items(Request $request)
    {
        Log::channel('sepidar')->info('Items POST data received', $request->all());

        SaveItemDataJob::dispatch($request->all());

        return response()->json(['message' => 'Items data received and queued for processing']);
    }

    public function grouping(Request $request)
    {
        Log::channel('sepidar')->info('Grouping POST data received', $request->all());

        SaveGroupingDataJob::dispatch($request->all());

        return response()->json(['message' => 'Grouping data received and queued for processing']);
    }
}
