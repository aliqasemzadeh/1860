<?php

namespace App\Console\Commands;

use App\Support\MarkaziPriceFetcher;
use Illuminate\Console\Command;

class FetchMarkaziPriceCommand extends Command
{
    protected $signature = 'markazi:price {url : The Markazi product URL} {--debug : Show detailed debugging information}';

    protected $description = 'Fetch product price from Markazi';

    public function handle(): int
    {
        $url = $this->argument('url');
        $debug = $this->option('debug');
        
        $this->info("Fetching price from: {$url}");
        
        if ($debug) {
            $this->line('Debug mode enabled');
        }

        try {
            $price = MarkaziPriceFetcher::fetchPrice($url, $debug ? $this : null);
            
            if ($price) {
                $this->info("Price: " . number_format($price) . " تومان");
                return 0;
            } else {
                $this->warn('Could not fetch price. The product might not be available or the page structure has changed.');
                if (!$debug) {
                    $this->warn('Try using --debug flag to see detailed debugging information.');
                }
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            if ($debug) {
                $this->error('Stack trace: ' . $e->getTraceAsString());
            }
            return 1;
        }
    }
}

