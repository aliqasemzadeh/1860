<?php

namespace App\Console\Commands\Shop;

use App\Jobs\Shop\PriceFetcher\UpdatePriceJob;
use Illuminate\Console\Command;

class PriceFetcherCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shop:update-price-fetchers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all price fetchers';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Dispatching price update job...');
        
        UpdatePriceJob::dispatch();
        
        $this->info('Price update job dispatched successfully.');
        
        return 0;
    }
}
