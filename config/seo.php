<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Currency (JSON-LD Product offers)
    |--------------------------------------------------------------------------
    |
    | Product prices in the database are stored in Toman. Schema.org / Google
    | expect IRR (Rial), so offers.price = toman * currency_multiplier.
    | Iranian shopping engines (Torob/Emalls meta tags) expect Toman as-is.
    |
    */
    'currency' => 'IRR',

    'currency_multiplier' => 10,

    'locale' => 'fa_IR',

    'description_limit' => 160,

    'per_page' => [
        'home' => 24,
        'category' => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes that should emit robots noindex,follow
    |--------------------------------------------------------------------------
    */
    'noindex_routes' => [
        'panel.*',
        'order.*',
        'login',
        'logout',
    ],

    /*
    |--------------------------------------------------------------------------
    | robots.txt Disallow paths
    |--------------------------------------------------------------------------
    */
    'robots' => [
        'disallow' => [
            '/panel/',
            '/cart',
            '/checkout',
            '/shipping',
            '/orders',
            '/login',
            '/logout',
            '/livewire/',
        ],
    ],

];
