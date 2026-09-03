<?php

namespace App\Jobs\Shop\PriceFetcher;

use App\Models\Shop\PriceFetcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class UpdatePriceJob implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $priceFetchers = PriceFetcher::query()
            ->whereHas('product', fn ($query) => $query->active())
            ->get();

        Log::info("Starting price update for {$priceFetchers->count()} price fetchers");

        foreach ($priceFetchers as $priceFetcher) {
            FetchPriceJob::dispatch($priceFetcher);
        }

        Log::info("Dispatched price update jobs for all price fetchers");
    }
}
