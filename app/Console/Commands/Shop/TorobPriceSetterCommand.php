<?php

namespace App\Console\Commands\Shop;

use App\Jobs\Shop\TorobPriceSetterJob;
use App\Models\Shop\TorobPriceSetter;
use Illuminate\Console\Command;
use Throwable;

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

        $processed = 0;
        $failed = 0;

        foreach ($setters as $setter) {
            try {
                if ($this->option('sync')) {
                    TorobPriceSetterJob::dispatchSync($setter);
                } else {
                    TorobPriceSetterJob::dispatch($setter);
                }

                $processed++;
            } catch (Throwable $exception) {
                $failed++;
                $this->components->warn(sprintf(
                    'Torob pricing rule #%d failed: %s',
                    $setter->getKey(),
                    mb_substr($exception->getMessage(), 0, 200),
                ));
            }
        }

        $this->components->info("Processed {$processed} Torob pricing rule(s).");

        if ($failed > 0) {
            $this->components->warn("{$failed} Torob pricing rule(s) failed.");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
