<?php

use Illuminate\Support\Facades\Schedule;

// Schedule: cancel unpaid orders every minute
Schedule::command('shop:cancel-unpaid-orders')->everyMinute();

// Schedule: auto-deliver shipped orders daily at 01:00
Schedule::command('shop:auto-deliver-orders')->dailyAt('01:00');

// Schedule: backup run daily at 06:00
Schedule::command('backup:run')->dailyAt('06:00');

Schedule::command('shop:refresh-torob-proxies --force')
    ->everyFiveMinutes()
    ->withoutOverlapping(5);

Schedule::command('shop:sync-torob-prices')
    ->everyFiveMinutes()
    ->withoutOverlapping(2);
