<?php

namespace App\Console\Commands\Shop;

use App\Support\TorobProxyPool;
use Illuminate\Console\Command;

class RefreshTorobProxiesCommand extends Command
{
    protected $signature = 'shop:refresh-torob-proxies {--force : Ignore the current proxy-list cache}';

    protected $description = 'Refresh and validate the cached Torob proxy pool';

    public function handle(TorobProxyPool $proxyPool): int
    {
        if (! $proxyPool->enabled()) {
            $this->components->info('Torob proxy support is disabled.');

            return self::SUCCESS;
        }

        $proxies = $proxyPool->refresh((bool) $this->option('force'));

        if ($proxies === []) {
            $this->components->warn('No eligible online Torob proxies are currently available.');

            return self::FAILURE;
        }

        $this->components->info('Cached '.count($proxies).' eligible Torob proxies.');

        return self::SUCCESS;
    }
}
