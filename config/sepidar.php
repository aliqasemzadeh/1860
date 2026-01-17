<?php

return [
    'FiscalYearRef' => 3,
    'tables' => [
        0 => [
            'model' => \App\Models\Sepidar\RPA\Bank::class,
            'endpoint' => 'banks',
            'row_id' => 'BankId',
        ],
        1 => [
            'model' => \App\Models\Sepidar\RPA\BankAccount::class,
            'endpoint' => 'bank-accounts',
            'row_id' => 'BankAccountId',
        ],
        2 => [
            'model' => \App\Models\Sepidar\RPA\BankAccountBalance::class,
            'endpoint' => 'bank-account-balances',
            'row_id' => 'BankAccountBalanceId',
        ],
        3 => [
            'model' => \App\Models\Sepidar\RPA\BankBranch::class,
            'endpoint' => 'bank-branches',
            'row_id' => 'BankBranchId',
        ],

        4 => [
            'model' => \App\Models\Sepidar\ACC\DL::class,
            'endpoint' => 'dls',
            'row_id' => 'DLId',
        ],

        5 => [
            'model' => \App\Models\Sepidar\INV\ItemStockSummary::class,
            'endpoint' => 'item-stock-summaries',
            'row_id' => 'ItemStockSummaryID',
        ],

        6 => [
            'model' => \App\Models\Sepidar\INV\Item::class,
            'endpoint' => 'items',
            'row_id' => 'ItemID',
        ],


        7 => [
            'model' => \App\Models\Sepidar\GNR\Grouping::class,
            'endpoint' => 'grouping',
            'row_id' => 'GroupingID',
        ],

        8 => [
            'model' => \App\Models\Sepidar\SLS\Invoice::class,
            'endpoint' => 'invoices',
            'row_id' => 'InvoiceId',
        ],

        9 => [
            'model' => \App\Models\Sepidar\SLS\InvoiceItem::class,
            'endpoint' => 'invoice-items',
            'row_id' => 'InvoiceItemID',
        ],

        10 => [
            'model' => \App\Models\Sepidar\INV\InventoryReceipt::class,
            'endpoint' => 'inventory-receipts',
            'row_id' => 'InventoryReceiptID',
        ],

        11 => [
            'model' => \App\Models\Sepidar\INV\InventoryReceiptItem::class,
            'endpoint' => 'inventory-receipt-items',
            'row_id' => 'InventoryReceiptItemID',
        ],
    ]
];
