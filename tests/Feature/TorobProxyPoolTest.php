<?php

use App\Support\TorobProxyPool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
    config()->set('proxy.torob', [
        'enabled' => true,
        'mode' => 'proxy_first',
        'manual' => [],
        'use_legacy_proxies' => false,
        'protocols' => ['https', 'socks5'],
        'anonymity' => ['elite'],
        'min_uptime_percent' => 80,
        'max_latency_ms' => 1500,
        'max_pool_size' => 300,
        'max_attempts' => 3,
        'lease_seconds' => 45,
        'failure_cooldown_seconds' => 900,
        'block_cooldown_seconds' => 3600,
        'source' => [
            'enabled' => true,
            'url' => 'https://proxy-source.test/data.json',
            'ttl' => 300,
            'last_good_ttl' => 86400,
            'timeout' => 10,
        ],
    ]);
});

test('proxy pool filters the online source and prioritizes manual proxies', function () {
    config()->set('proxy.torob.manual', [
        'http://user:secret@manual-proxy.test:8080',
    ]);
    Http::fake([
        'proxy-source.test/*' => Http::response([
            proxySourceRow('https', '1.1.1.1', 443, 99, 100),
            proxySourceRow('socks5', '8.8.8.8', 1080, 95, 200),
            proxySourceRow('http', '9.9.9.9', 8080, 99, 100),
            proxySourceRow('https', '127.0.0.1', 443, 99, 100),
            proxySourceRow('https', '208.67.222.222', 443, 20, 100),
        ]),
    ]);

    $pool = app(TorobProxyPool::class);
    $online = $pool->refresh(true);
    $candidates = $pool->leaseCandidates(3);

    expect($online)->toHaveCount(2)
        ->and(array_column($online, 'uri'))->toContain('https://1.1.1.1:443', 'socks5h://8.8.8.8:1080')
        ->and($candidates)->toHaveCount(3)
        ->and($candidates[0]['source'])->toBe('manual')
        ->and($candidates[0]['uri'])->toBe('http://user:secret@manual-proxy.test:8080');
});

test('proxy pool quarantines a failed proxy and rotates to the next one', function () {
    config()->set('proxy.torob.source.enabled', false);
    config()->set('proxy.torob.use_legacy_proxies', false);
    config()->set('proxy.torob.manual', [
        'http://192.0.2.10:8080',
        'http://192.0.2.11:8080',
    ]);

    $pool = app(TorobProxyPool::class);
    $first = $pool->leaseCandidates(1)[0];
    $pool->markFailure($first, 'HTTP 490', 490);
    $second = $pool->leaseCandidates(1)[0];

    expect($first['id'])->not->toBe($second['id'])
        ->and($second['uri'])->toBe('http://192.0.2.11:8080');
});

test('proxy pool uses the last good list when refreshing the source fails', function () {
    Http::fake([
        'proxy-source.test/*' => Http::response([
            proxySourceRow('https', '1.1.1.1', 443, 99, 100),
        ]),
    ]);

    $pool = app(TorobProxyPool::class);
    $fresh = $pool->refresh(true);
    Http::fake([
        'proxy-source.test/*' => Http::response([], 503),
    ]);
    $fallback = $pool->refresh(true);

    expect($fresh)->toHaveCount(1)
        ->and($fallback)->toBe($fresh);
});

test('proxy refresh command caches eligible proxies', function () {
    Http::fake([
        'proxy-source.test/*' => Http::response([
            proxySourceRow('https', '1.1.1.1', 443, 99, 100),
        ]),
    ]);

    $this->artisan('shop:refresh-torob-proxies --force')
        ->expectsOutputToContain('Cached 1 eligible Torob proxies.')
        ->assertSuccessful();
});

test('proxy pool can include legacy proxies and clear quarantines', function () {
    config()->set('proxy.torob.source.enabled', false);
    config()->set('proxy.torob.use_legacy_proxies', true);
    config()->set('proxy.torob.manual', []);
    config()->set('proxy.proxies', [
        '192.0.2.20:8080',
        '192.0.2.21:8080',
    ]);

    $pool = app(TorobProxyPool::class);
    $first = $pool->leaseCandidates(1)[0];
    $pool->markFailure($first, 'HTTP 490', 490);

    expect($first['source'])->toBe('legacy')
        ->and($pool->stats()['legacy'])->toBe(2)
        ->and($pool->stats()['quarantined'])->toBe(1)
        ->and($pool->clearQuarantines())->toBe(1)
        ->and($pool->stats()['quarantined'])->toBe(0);
});

test('torob cooldown command clears direct and proxy quarantines', function () {
    config()->set('proxy.torob.source.enabled', false);
    config()->set('proxy.torob.use_legacy_proxies', false);
    config()->set('proxy.torob.manual', ['http://192.0.2.10:8080']);

    Cache::put('torob:sellers:direct-blocked-until', now()->addMinutes(10)->timestamp, now()->addMinutes(10));

    $pool = app(TorobProxyPool::class);
    $proxy = $pool->leaseCandidates(1)[0];
    $pool->markFailure($proxy, 'HTTP 490', 490);

    $this->artisan('shop:clear-torob-cooldown --proxies')
        ->expectsOutputToContain('Cleared Torob direct cooldown')
        ->expectsOutputToContain('Cleared 1 quarantined Torob proxy/proxies.')
        ->assertSuccessful();

    expect(app(\App\Support\TorobOfferFetcher::class)->isDirectBlocked())->toBeFalse()
        ->and($pool->stats()['quarantined'])->toBe(0);
});

/** @return array<string, mixed> */
function proxySourceRow(string $protocol, string $ip, int $port, float $uptime, float $latency): array
{
    return [
        'protocol' => $protocol,
        'ip' => $ip,
        'port' => $port,
        'country' => 'Test',
        'country_code' => 'TS',
        'city' => 'Test',
        'anonymity' => 'elite',
        'ssl' => true,
        'uptime_percent' => $uptime,
        'latency_ms' => $latency,
        'last_checked' => now()->timestamp,
    ];
}
