<?php

namespace App\Console\Commands\Shop;

use App\Support\TorobOfferFetcher;
use App\Support\TorobProxyPool;
use Illuminate\Console\Command;

class ClearTorobCooldownCommand extends Command
{
    protected $signature = 'shop:clear-torob-cooldown
                            {--proxies : Also clear quarantined Torob proxies}';

    protected $description = 'Clear Torob direct-request cooldown and optionally proxy quarantines';

    public function handle(TorobOfferFetcher $offerFetcher, TorobProxyPool $proxyPool): int
    {
        $clearedDirect = false;

        if ($offerFetcher->isDirectBlocked()) {
            $blockedUntil = $offerFetcher->directBlockedUntil();
            $offerFetcher->clearDirectCooldown();
            $clearedDirect = true;
            $this->components->info('Cleared Torob direct cooldown that was active until '.date('Y-m-d H:i:s', $blockedUntil).'.');
        }

        $clearedProxies = 0;

        if ($this->option('proxies')) {
            $clearedProxies = $proxyPool->clearQuarantines();
            $this->components->info("Cleared {$clearedProxies} quarantined Torob proxy/proxies.");
        }

        if (! $clearedDirect && $clearedProxies === 0) {
            $stats = $proxyPool->stats();
            $this->components->info('No Torob cooldowns were active.');
            $this->components->twoColumnDetail('Manual proxies', (string) $stats['manual']);
            $this->components->twoColumnDetail('Legacy proxies', (string) $stats['legacy']);
            $this->components->twoColumnDetail('Online proxies', (string) $stats['online']);
            $this->components->twoColumnDetail('Available proxies', (string) $stats['available']);
            $this->components->twoColumnDetail('Quarantined proxies', (string) $stats['quarantined']);
        }

        return self::SUCCESS;
    }
}
