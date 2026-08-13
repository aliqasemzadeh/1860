<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


// Schedule: cancel unpaid orders every minute
Schedule::command('shop:cancel-unpaid-orders')->everyMinute();

// Schedule: auto-deliver shipped orders daily at 01:00
Schedule::command('shop:auto-deliver-orders')->dailyAt('01:00');
