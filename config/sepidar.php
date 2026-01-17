<?php

return [
    'FiscalYearRef' => 3,
    'tables' => [
        'banks' => [
            'model' => \App\Models\Sepidar\RPA\Bank::class,
            'endpoint' => 'banks',
            'row_id' => 'BankId',
        ],
        'bank-accounts' => [
            'model' => \App\Models\Sepidar\RPA\BankAccount::class,
            'endpoint' => 'bank-accounts',
            'row_id' => 'BankAccountId',
        ],
        'bank-account-balances' => [
            'model' => \App\Models\Sepidar\RPA\BankAccountBalance::class,
            'endpoint' => 'bank-account-balances',
            'row_id' => 'BankAccountBalanceId',
        ],
        'bank-branches' => [
            'model' => \App\Models\Sepidar\RPA\BankBranch::class,
            'endpoint' => 'bank-branches',
            'row_id' => 'BankBranchId',
        ],

        'dls' => [
            'model' => \App\Models\Sepidar\ACC\DL::class,
            'endpoint' => 'dls',
            'row_id' => 'DLId',
        ],

        'item-stock-summaries' => [
            'model' => \App\Models\Sepidar\INV\ItemStockSummary::class,
            'endpoint' => 'item-stock-summaries',
            'row_id' => 'ItemStockSummaryID',
        ],

        'items' => [
            'model' => \App\Models\Sepidar\INV\Item::class,
            'endpoint' => 'items',
            'row_id' => 'ItemID',
        ],


        'grouping' => [
            'model' => \App\Models\Sepidar\GNR\Grouping::class,
            'endpoint' => 'grouping',
            'row_id' => 'GroupingID',
        ],

        'invoices' => [
            'model' => \App\Models\Sepidar\SLS\Invoice::class,
            'endpoint' => 'invoices',
            'row_id' => 'InvoiceId',
        ],

        'invoice-items' => [
            'model' => \App\Models\Sepidar\SLS\InvoiceItem::class,
            'endpoint' => 'invoice-items',
            'row_id' => 'InvoiceItemID',
        ],

        'inventory-receipts' => [
            'model' => \App\Models\Sepidar\INV\InventoryReceipt::class,
            'endpoint' => 'inventory-receipts',
            'row_id' => 'InventoryReceiptID',
        ],

        'inventory-receipt-items' => [
            'model' => \App\Models\Sepidar\INV\InventoryReceiptItem::class,
            'endpoint' => 'inventory-receipt-items',
            'row_id' => 'InventoryReceiptItemID',
        ],
    ]
];
