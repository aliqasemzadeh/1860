<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


// Schedule: remove old remittances every day at 00:00:00
Schedule::command('app:remove-old-remittances-command')->dailyAt('00:00');

// Schedule: update price fetchers every day at 22:00:00 (10 PM)
Schedule::command('shop:update-price-fetchers')->dailyAt('22:00');
