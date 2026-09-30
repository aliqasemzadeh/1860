<?php

namespace App\Console\Commands\Shop;

use App\Jobs\Shop\SetareganPriceSetterJob;
use App\Models\Shop\SetareganPriceSetter;
use Illuminate\Console\Command;
use Throwable;

class SetareganPriceSetterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shop:sync-setaregan-prices
                            {--rule= : Process only one Setaregan price setter ID}
                            {--sync : Run jobs synchronously}
                            {--sleep=1 : Seconds to wait between rules in sync mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Setaregan supplier prices and update active product prices and stock';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = SetareganPriceSetter::query()
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
            $this->components->info('No active Setaregan pricing rules found.');

            return self::SUCCESS;
        }

        $processed = 0;
        $failed = 0;
        $sleepSeconds = max(0, (int) $this->option('sleep'));

        foreach ($setters as $index => $setter) {
            try {
                if ($this->option('sync')) {
                    SetareganPriceSetterJob::dispatchSync($setter);
                } else {
                    SetareganPriceSetterJob::dispatch($setter);
                }

                $processed++;
            } catch (Throwable $exception) {
                $failed++;
                $this->components->warn(sprintf(
                    'Setaregan pricing rule #%d failed: %s',
                    $setter->getKey(),
                    mb_substr($exception->getMessage(), 0, 200),
                ));
            }

            if ($this->option('sync') && $sleepSeconds > 0 && $index < $setters->count() - 1) {
                sleep($sleepSeconds);
            }
        }

        $this->components->info("Processed {$processed} Setaregan pricing rule(s).");

        if ($failed > 0) {
            $this->components->warn("{$failed} Setaregan pricing rule(s) failed.");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
