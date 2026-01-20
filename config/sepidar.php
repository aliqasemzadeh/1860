<?php

return [
    'FiscalYearRef' => 3,
    'tables' => [
        'banks' => [
            'model' => "\App\Models\Sepidar\RPA\Bank",
            'endpoint' => 'banks',
            'row_id' => 'BankId',
        ],
        'bank_accounts' => [
            'model' => "\App\Models\Sepidar\RPA\BankAccount",
            'endpoint' => 'bank-accounts',
            'row_id' => 'BankAccountId',
        ],
        'bank_account_balances' => [
            'model' => "\App\Models\Sepidar\RPA\BankAccountBalance",
            'endpoint' => 'bank-account-balances',
            'row_id' => 'BankAccountBalanceId',
        ],
        'bank_branches' => [
            'model' => "\App\Models\Sepidar\RPA\BankBranch",
            'endpoint' => 'bank-branches',
            'row_id' => 'BankBranchId',
        ],

        'dls' => [
            'model' => "\App\Models\Sepidar\ACC\DL",
            'endpoint' => 'dls',
            'row_id' => 'DLId',
        ],

        'item_stock_summaries' => [
            'model' => "\App\Models\Sepidar\INV\ItemStockSummary",
            'endpoint' => 'item-stock-summaries',
            'row_id' => 'ItemStockSummaryID',
        ],

        'items' => [
            'model' => "\App\Models\Sepidar\INV\Item",
            'endpoint' => 'items',
            'row_id' => 'ItemID',
        ],


        'groupings' => [
            'model' => "\App\Models\Sepidar\GNR\Grouping",
            'endpoint' => 'grouping',
            'row_id' => 'GroupingID',
        ],

        'invoices' => [
            'model' => "\App\Models\Sepidar\SLS\Invoice",
            'endpoint' => 'invoices',
            'row_id' => 'InvoiceId',
        ],

        'invoice_items' => [
            'model' => "\App\Models\Sepidar\SLS\InvoiceItem",
            'endpoint' => 'invoice-items',
            'row_id' => 'InvoiceItemID',
        ],

        'inventory_receipts' => [
            'model' => "\App\Models\Sepidar\INV\InventoryReceipt",
            'endpoint' => 'inventory-receipts',
            'row_id' => 'InventoryReceiptID',
        ],

        'inventory_receipt_items' => [
            'model' => "\App\Models\Sepidar\INV\InventoryReceiptItem",
            'endpoint' => 'inventory-receipt-items',
            'row_id' => 'InventoryReceiptItemID',
        ],

        'parties' => [
            'model' => "\App\Models\Sepidar\GNR\Party",
            'endpoint' => 'parties',
            'row_id' => 'PartyId',
        ],

        'party_phones' => [
            'model' => "\App\Models\Sepidar\GNR\PartyPhone",
            'endpoint' => 'party-phones',
            'row_id' => 'PartyPhoneId',
        ],

        'party_phones' => [
            'model' => "\App\Models\Sepidar\GNR\PartyPhone",
            'endpoint' => 'party-phones',
            'row_id' => 'PartyPhoneId',
        ],

    ]
];
