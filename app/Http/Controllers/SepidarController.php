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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SepidarController extends Controller
{
    public function invoices(Request $request)
    {
        //Log::channel('sepidar')->info('Invoice POST data received', $request->all());

        SaveInvoiceDataJob::dispatch($request->all());

        return response()->json(['message' => 'Invoice data received and queued for processing']);
    }

    public function invoice_items(Request $request)
    {
        //Log::channel('sepidar')->info('Invoice Items POST data received', $request->all());

        SaveInvoiceItemDataJob::dispatch($request->all());

        return response()->json(['message' => 'Invoice data received and queued for processing']);
    }

    public function banks(Request $request)
    {
        //Log::channel('sepidar')->info('Bank POST data received', $request->all());

        SaveBankDataJob::dispatch($request->all());

        return response()->json(['message' => 'Bank account data received and queued for processing']);
    }


    public function bank_accounts(Request $request)
    {
        //Log::channel('sepidar')->info('Bank Account POST data received', $request->all());

        SaveBankAccountDataJob::dispatch($request->all());

        return response()->json(['message' => 'Bank account data received and queued for processing']);
    }

    public function bank_account_balances(Request $request)
    {
        //Log::channel('sepidar')->info('Bank Account Balances POST data received', $request->all());

        SaveBankAccountBalanceDataJob::dispatch($request->all());

        return response()->json(['message' => 'Bank account data received and queued for processing']);
    }

    public function bank_branches(Request $request)
    {
        //Log::channel('sepidar')->info('Bank Account Balances POST data received', $request->all());

        SaveBankBranchDataJob::dispatch($request->all());

        return response()->json(['message' => 'Bank Branch data received and queued for processing']);
    }

    public function items(Request $request)
    {
        //Log::channel('sepidar')->info('Items POST data received', $request->all());

        SaveItemDataJob::dispatch($request->all());

        return response()->json(['message' => 'Items data received and queued for processing']);
    }

    public function item_images(Request $request)
    {
        ///Log::channel('sepidar')->info('Items POST data received', $request->all());

        //SaveItemImageDataJob::dispatch($request->all());

        return response()->json(['message' => 'Item Image data received and queued for processing']);
    }

    public function item_stock_summaries(Request $request)
    {
        //Log::channel('sepidar')->info('Item Stock Summary POST data received', $request->all());

        SaveItemStockSummaryDataJob::dispatch($request->all());

        return response()->json(['message' => 'Item Stock Summary data received and queued for processing']);
    }

    public function grouping(Request $request)
    {
        //Log::channel('sepidar')->info('Grouping POST data received', $request->all());

        SaveGroupingDataJob::dispatch($request->all());

        return response()->json(['message' => 'Grouping data received and queued for processing']);
    }

    public function inventory_receipts(Request $request)
    {
        //Log::channel('sepidar')->info('Inventory Receipt POST data received', $request->all());

        SaveInventoryReceiptDataJob::dispatch($request->all());

        return response()->json(['message' => 'Inventory Receipt data received and queued for processing']);
    }

    public function inventory_receipt_items(Request $request)
    {
        //Log::channel('sepidar')->info('Inventory Receipt Items POST data received', $request->all());

        SaveInventoryReceiptItemDataJob::dispatch($request->all());

        return response()->json(['message' => 'Inventory Receipt Items data received and queued for processing']);
    }

    public function dls(Request $request)
    {
        //Log::channel('sepidar')->info('DLs POST data received', $request->all());

        SaveDLDataJob::dispatch($request->all());

        return response()->json(['message' => 'DLs data received and queued for processing']);
    }

    public function parties(Request $request)
    {
        //Log::channel('sepidar')->info('Party POST data received', $request->all());

        SavePartyDataJob::dispatch($request->all());

        return response()->json(['message' => 'Party data received and queued for processing']);
    }

    public function party_phones(Request $request)
    {
        //Log::channel('sepidar')->info('Party Phone POST data received', $request->all());

        SavePartyPhoneDataJob::dispatch($request->all());

        return response()->json(['message' => 'Party Phone data received and queued for processing']);
    }
}
