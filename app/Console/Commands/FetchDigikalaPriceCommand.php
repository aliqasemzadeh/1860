<?php

namespace App\Console\Commands;

use App\Support\DigikalaPriceFetcher;
use Illuminate\Console\Command;

class FetchDigikalaPriceCommand extends Command
{
    protected $signature = 'digikala:price {url : The Digikala product URL}';

    protected $description = 'Fetch product price from Digikala';

    public function handle(): int
    {
        $url = $this->argument('url');

        // Extract product ID from URL
        $productId = $this->extractProductId($url);

        if (!$productId) {
            $this->error('Could not extract product ID from URL');
            return 1;
        }

        $this->info("Fetching price for product ID: {$productId}");

        try {
            // Use the DigikalaPriceFetcher service
            $price = DigikalaPriceFetcher::fetchPrice($url);

            if ($price) {
                $this->info("Price: " . number_format($price) . " تومان");
                return 0;
            } else {
                $this->warn('Could not fetch price. The product might not be available or the page structure has changed.');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}

