<?php

return [
    'admin_mobile' => env('SHOP_ADMIN_MOBILE', '09177886099'),
    'unpaid_cancel_minutes' => (int) env('SHOP_UNPAID_CANCEL_MINUTES', 10),
    'auto_deliver_days' => (int) env('SHOP_AUTO_DELIVER_DAYS', 10),
];
