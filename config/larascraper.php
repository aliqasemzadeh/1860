<?php

use EduLazaro\Larascraper\Runners\HttpRunner;

return [
    'proxies' => [],

    'http_user_agent' => HttpRunner::DEFAULT_USER_AGENT,

    'throttle' => [
        'torob.sellers' => [
            'interval' => (int) env('TOROB_REQUEST_INTERVAL_SECONDS', 15),
            'lock_base' => 3600,
            'lock_max' => 3600,
            'max_wait' => 60,
        ],
    ],
];
