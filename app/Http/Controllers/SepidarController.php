<?php

namespace App\Http\Controllers;

use App\Jobs\Sepidar\SaveBankDataJob;
use App\Jobs\Sepidar\SaveBankAccountDataJob;
use App\Jobs\Sepidar\SaveBankAccountBalanceDataJob;
use App\Jobs\Sepidar\SaveBankBranchDataJob;
use App\Jobs\Sepidar\SaveDLDataJob;
use App\Jobs\Sepidar\SaveInventoryReceiptDataJob;
use App\Jobs\Sepidar\SaveInventoryReceiptItemDataJob;
use App\Jobs\Sepidar\SaveInvoiceDataJob;
use App\Jobs\Sepidar\SaveInvoiceItemDataJob;
use App\Jobs\Sepidar\SaveItemDataJob;
use App\Jobs\Sepidar\SaveItemStockSummaryDataJob;
use App\Jobs\Sepidar\SavePartyDataJob;
use App\Jobs\Sepidar\SavePartyPhoneDataJob;
use App\Jobs\Sepidar\SaveGroupingDataJob;
use App\Jobs\Sepidar\SaveTableDataJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SepidarController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'table' => 'required|string',
            'data' => 'required|array',
            'clean' => 'required|boolean',
        ]);

        if(sizeof($request->data)) {
            Log::channel('sepidar')->info('Remote Data: ', [
                'table' => $request->table,
                'data' => sizeof($request->data),
                'clean' => $request->clean,
            ]);

            SaveTableDataJob::dispatch($request->data, $request->table, $request->clean);
        }

        return response()->json(['message' => 'Data received and queued for processing.']);
    }
}
