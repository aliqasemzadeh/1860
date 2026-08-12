<?php

$a = include __DIR__.'/lang/fa/app.php';
$keys = [
    'all_statuses', 'default', 'address_saved', 'address_deleted', 'address_name',
    'province', 'city', 'are_you_sure', 'save', 'order_status_completed',
    'order_status_processing', 'shipping_cost', 'subtotal', 'order_items',
    'order_details', 'profile', 'my_addresses', 'paid', 'unpaid',
];

foreach ($keys as $k) {
    echo $k.': '.(array_key_exists($k, $a) ? 'OK' : 'MISSING').PHP_EOL;
}

try {
    require __DIR__.'/lang/fa/app.php';
    echo "app.php: syntax OK\n";
} catch (Throwable $e) {
    echo 'app.php ERROR: '.$e->getMessage().PHP_EOL;
}
