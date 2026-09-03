<?php

namespace App\Console\Commands\Shop;

use App\Jobs\Shop\TorobPriceSetterJob;
use App\Models\Shop\TorobPriceSetter;
use Illuminate\Console\Command;

class TorobPriceSetterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shop:sync-torob-prices
                            {--rule= : Process only one Torob price setter ID}
                            {--sync : Run jobs synchronously}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Torob competitors and update active product prices';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = TorobPriceSetter::query()
            ->where('is_active', true)
            ->whereHas('priceFetcher.product', fn ($query) => $query->active());

        if ($ruleId = $this->option('rule')) {
            $query->whereKey((int) $ruleId);
        }

        $setters = $query
            ->orderByRaw('CASE WHEN last_checked_at IS NULL THEN 0 ELSE 1 END')
            ->orderBy('last_checked_at')
            ->orderBy('id')
            ->get();

        if ($setters->isEmpty()) {
            $this->components->info('No active Torob pricing rules found.');

            return self::SUCCESS;
        }

        foreach ($setters as $setter) {
            if ($this->option('sync')) {
                TorobPriceSetterJob::dispatchSync($setter);
            } else {
                TorobPriceSetterJob::dispatch($setter);
            }
        }

        $this->components->info("Processed {$setters->count()} Torob pricing rule(s).");

        return self::SUCCESS;
    }
}
