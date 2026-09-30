<?php

namespace App\Console\Commands;

use App\Support\SetareganPriceFetcher;
use Illuminate\Console\Command;

class FetchSetareganPriceCommand extends Command
{
    protected $signature = 'setaregan:price {url : The Setaregan product URL} {--debug : Show detailed debugging information}';

    protected $description = 'Fetch product price and availability from Setaregan';

    public function handle(): int
    {
        $url = $this->argument('url');
        $debug = $this->option('debug');

        $this->info("Fetching offer from: {$url}");

        if ($debug) {
            $this->line('Debug mode enabled');
        }

        try {
            $offer = SetareganPriceFetcher::fetchOffer($url, $debug ? $this : null);

            if ($offer['price'] !== null) {
                $this->info('Price: '.number_format($offer['price']).' تومان');
            } else {
                $this->warn('Could not fetch price. The product might not be available or the page structure has changed.');
            }

            $this->info('Available: '.($offer['available'] ? 'yes' : 'no'));

            if ($offer['source']) {
                $this->line('Source: '.$offer['source']);
            }

            return $offer['price'] !== null || $offer['available'] === false ? self::SUCCESS : self::FAILURE;
        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());
            if ($debug) {
                $this->error('Stack trace: '.$e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }
}
