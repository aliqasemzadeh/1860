<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class TorobProxyPool
{
    private const LIST_CACHE_KEY = 'torob:proxy:list';

    private const LAST_GOOD_CACHE_KEY = 'torob:proxy:last-good';

    private const REFRESH_LOCK_KEY = 'torob:proxy:refresh-lock';

    public function enabled(): bool
    {
        return (bool) config('proxy.torob.enabled', true);
    }

    /** @return list<array{uri: string, id: string, source: string, uptime_percent: float, latency_ms: float}> */
    public function refresh(bool $force = false): array
    {
        if (! $this->enabled()) {
            return [];
        }

        if (! (bool) config('proxy.torob.source.enabled', true)) {
            return $this->cachedOnlineProxies();
        }

        $cached = Cache::get(self::LIST_CACHE_KEY);
        if (! $force && is_array($cached)) {
            return $cached;
        }

        $lock = Cache::lock(self::REFRESH_LOCK_KEY, 30);
        if (! $lock->get()) {
            $fallback = $this->cachedOnlineProxies();
            Cache::put(self::LIST_CACHE_KEY, $fallback, now()->addMinute());

            return $fallback;
        }

        try {
            $cached = Cache::get(self::LIST_CACHE_KEY);
            if (! $force && is_array($cached)) {
                return $cached;
            }

            $response = Http::acceptJson()
                ->timeout((int) config('proxy.torob.source.timeout', 10))
                ->connectTimeout(5)
                ->retry([300, 800], throw: false)
                ->get((string) config('proxy.torob.source.url'));

            if (! $response->successful()) {
                throw new RuntimeException("Proxy source returned HTTP {$response->status()}.");
            }

            $payload = $response->json();
            if (! is_array($payload)) {
                throw new RuntimeException('Proxy source returned invalid JSON.');
            }

            $proxies = $this->filterOnlineProxies($payload);
            if ($proxies === []) {
                throw new RuntimeException('Proxy source contained no eligible proxies.');
            }

            Cache::put(
                self::LIST_CACHE_KEY,
                $proxies,
                now()->addSeconds((int) config('proxy.torob.source.ttl', 300)),
            );
            Cache::put(
                self::LAST_GOOD_CACHE_KEY,
                $proxies,
                now()->addSeconds((int) config('proxy.torob.source.last_good_ttl', 86400)),
            );

            return $proxies;
        } catch (Throwable $exception) {
            Log::warning('Torob proxy source refresh failed.', [
                'error' => mb_substr($exception->getMessage(), 0, 500),
            ]);

            $fallback = $this->cachedOnlineProxies();
            Cache::put(self::LIST_CACHE_KEY, $fallback, now()->addMinute());

            return $fallback;
        } finally {
            $lock->release();
        }
    }

    /** @return list<array{uri: string, id: string, source: string, uptime_percent: float, latency_ms: float}> */
    public function leaseCandidates(?int $limit = null): array
    {
        if (! $this->enabled()) {
            return [];
        }

        $limit ??= (int) config('proxy.torob.max_attempts', 10);
        $queue = array_merge(
            $this->rotate($this->configuredManualProxies(), 'manual'),
            $this->rotate($this->prioritizeHealthy($this->refresh()), 'online'),
            $this->rotate($this->legacyProxies(), 'legacy'),
        );
        $selected = [];

        foreach ($queue as $proxy) {
            if (count($selected) >= max(0, $limit)) {
                break;
            }

            if ($this->isQuarantined($proxy['id'])) {
                continue;
            }

            if (! Cache::add($this->leaseKey($proxy['id']), true, now()->addSeconds(
                (int) config('proxy.torob.lease_seconds', 45),
            ))) {
                continue;
            }

            $selected[] = $proxy;
        }

        return $selected;
    }

    /** @param array{uri: string, id: string, source: string, uptime_percent: float, latency_ms: float} $proxy */
    public function markSuccess(array $proxy, int $latencyMs): void
    {
        Cache::forget($this->leaseKey($proxy['id']));
        Cache::forget($this->quarantineKey($proxy['id']));
        Cache::put($this->successKey($proxy['id']), [
            'at' => now()->timestamp,
            'latency_ms' => $latencyMs,
        ], now()->addDay());

        Log::info('Torob proxy request succeeded.', [
            'proxy_id' => $proxy['id'],
            'source' => $proxy['source'],
            'latency_ms' => $latencyMs,
        ]);
    }

    /** @param array{uri: string, id: string, source: string, uptime_percent: float, latency_ms: float} $proxy */
    public function markFailure(array $proxy, string $reason, ?int $status = null): void
    {
        Cache::forget($this->leaseKey($proxy['id']));
        $seconds = in_array($status, [429, 490], true)
            ? (int) config('proxy.torob.block_cooldown_seconds', 3600)
            : (int) config('proxy.torob.failure_cooldown_seconds', 900);

        Cache::put($this->quarantineKey($proxy['id']), true, now()->addSeconds(max(60, $seconds)));

        Log::warning('Torob proxy was quarantined.', [
            'proxy_id' => $proxy['id'],
            'source' => $proxy['source'],
            'status' => $status,
            'reason' => mb_substr(str_replace($proxy['uri'], '[proxy]', $reason), 0, 500),
            'cooldown_seconds' => max(60, $seconds),
        ]);
    }

    /** @param array{uri: string, id: string, source: string, uptime_percent: float, latency_ms: float} $proxy */
    public function release(array $proxy): void
    {
        Cache::forget($this->leaseKey($proxy['id']));
    }

    /** @return array{manual: int, legacy: int, online: int, total: int, quarantined: int, available: int} */
    public function stats(): array
    {
        $manual = $this->configuredManualProxies();
        $legacy = $this->legacyProxies();
        $online = $this->cachedOnlineProxies();
        $unique = [];

        foreach (array_merge($manual, $online, $legacy) as $proxy) {
            $unique[$proxy['id']] = $proxy;
        }

        $quarantined = 0;

        foreach ($unique as $proxy) {
            if ($this->isQuarantined($proxy['id'])) {
                $quarantined++;
            }
        }

        return [
            'manual' => count($manual),
            'legacy' => count($legacy),
            'online' => count($online),
            'total' => count($unique),
            'quarantined' => $quarantined,
            'available' => count($unique) - $quarantined,
        ];
    }

    public function clearQuarantines(): int
    {
        $cleared = 0;

        foreach ($this->allKnownProxies() as $proxy) {
            if (! $this->isQuarantined($proxy['id'])) {
                continue;
            }

            Cache::forget($this->quarantineKey($proxy['id']));
            $cleared++;
        }

        return $cleared;
    }

    /** @return list<array{uri: string, id: string, source: string, uptime_percent: float, latency_ms: float}> */
    private function cachedOnlineProxies(): array
    {
        $cached = Cache::get(self::LIST_CACHE_KEY, Cache::get(self::LAST_GOOD_CACHE_KEY, []));

        return is_array($cached) ? $cached : [];
    }

    /** @param array<int, mixed> $rows
     * @return list<array{uri: string, id: string, source: string, uptime_percent: float, latency_ms: float}>
     */
    private function filterOnlineProxies(array $rows): array
    {
        $protocols = array_map('strtolower', (array) config('proxy.torob.protocols', ['https', 'socks5']));
        $anonymity = array_map('strtolower', (array) config('proxy.torob.anonymity', ['elite']));
        $minimumUptime = (float) config('proxy.torob.min_uptime_percent', 80);
        $maximumLatency = (float) config('proxy.torob.max_latency_ms', 1500);
        $proxies = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $protocol = strtolower((string) ($row['protocol'] ?? ''));
            $ip = (string) ($row['ip'] ?? '');
            $port = filter_var($row['port'] ?? null, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1, 'max_range' => 65535],
            ]);
            $uptime = is_numeric($row['uptime_percent'] ?? null) ? (float) $row['uptime_percent'] : 0.0;
            $latency = is_numeric($row['latency_ms'] ?? null) ? (float) $row['latency_ms'] : INF;
            $rowAnonymity = strtolower((string) ($row['anonymity'] ?? ''));

            if (! in_array($protocol, $protocols, true)
                || ! in_array($rowAnonymity, $anonymity, true)
                || $port === false
                || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
                || $uptime < $minimumUptime
                || $latency > $maximumLatency
                || ($protocol === 'https' && ($row['ssl'] ?? false) !== true)) {
                continue;
            }

            $scheme = $protocol === 'socks5' ? 'socks5h' : $protocol;
            $formattedIp = str_contains($ip, ':') ? "[{$ip}]" : $ip;
            $uri = "{$scheme}://{$formattedIp}:{$port}";
            $proxies[$this->proxyId($uri)] = $this->endpoint($uri, 'proxyscrape', $uptime, $latency);
        }

        $proxies = array_values($proxies);
        usort($proxies, fn (array $left, array $right): int => [
            -$left['uptime_percent'],
            $left['latency_ms'],
        ] <=> [
            -$right['uptime_percent'],
            $right['latency_ms'],
        ]);

        return array_slice($proxies, 0, (int) config('proxy.torob.max_pool_size', 300));
    }

    /** @return list<array{uri: string, id: string, source: string, uptime_percent: float, latency_ms: float}> */
    private function allKnownProxies(): array
    {
        return array_values(array_merge(
            $this->configuredManualProxies(),
            $this->cachedOnlineProxies(),
            $this->legacyProxies(),
        ));
    }

    /** @return list<array{uri: string, id: string, source: string, uptime_percent: float, latency_ms: float}> */
    private function configuredManualProxies(): array
    {
        $proxies = [];

        foreach ((array) config('proxy.torob.manual', []) as $value) {
            $uri = $this->normalizeManualProxy($value);
            if ($uri === null) {
                continue;
            }

            $proxies[$this->proxyId($uri)] = $this->endpoint($uri, 'manual', 100, 0);
        }

        return array_values($proxies);
    }

    /** @return list<array{uri: string, id: string, source: string, uptime_percent: float, latency_ms: float}> */
    private function legacyProxies(): array
    {
        if (! (bool) config('proxy.torob.use_legacy_proxies', true)) {
            return [];
        }

        $proxies = [];

        foreach ((array) config('proxy.proxies', []) as $value) {
            $uri = $this->normalizeManualProxy($value);
            if ($uri === null) {
                continue;
            }

            $proxies[$this->proxyId($uri)] = $this->endpoint($uri, 'legacy', 100, 0);
        }

        return array_values($proxies);
    }

    private function normalizeManualProxy(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $uri = trim($value);
        if (! str_contains($uri, '://')) {
            $uri = 'http://'.$uri;
        }

        $parts = parse_url($uri);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = (string) ($parts['host'] ?? '');
        $port = filter_var($parts['port'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 65535],
        ]);

        if (! in_array($scheme, ['http', 'https', 'socks5', 'socks5h'], true)
            || $host === '' || $port === false) {
            return null;
        }

        $scheme = $scheme === 'socks5' ? 'socks5h' : $scheme;
        $credentials = '';
        if (isset($parts['user'])) {
            $credentials = rawurlencode((string) $parts['user']);
            if (isset($parts['pass'])) {
                $credentials .= ':'.rawurlencode((string) $parts['pass']);
            }
            $credentials .= '@';
        }

        $formattedHost = str_contains($host, ':') ? "[{$host}]" : $host;

        return "{$scheme}://{$credentials}{$formattedHost}:{$port}";
    }

    /** @param list<array{uri: string, id: string, source: string, uptime_percent: float, latency_ms: float}> $proxies
     * @return list<array{uri: string, id: string, source: string, uptime_percent: float, latency_ms: float}>
     */
    private function prioritizeHealthy(array $proxies): array
    {
        usort($proxies, function (array $left, array $right): int {
            $leftHealthy = Cache::has($this->successKey($left['id'])) ? 1 : 0;
            $rightHealthy = Cache::has($this->successKey($right['id'])) ? 1 : 0;

            return [$rightHealthy, $right['uptime_percent'], -$right['latency_ms']]
                <=> [$leftHealthy, $left['uptime_percent'], -$left['latency_ms']];
        });

        return $proxies;
    }

    /** @param list<array{uri: string, id: string, source: string, uptime_percent: float, latency_ms: float}> $proxies
     * @return list<array{uri: string, id: string, source: string, uptime_percent: float, latency_ms: float}>
     */
    private function rotate(array $proxies, string $group): array
    {
        if (count($proxies) < 2) {
            return $proxies;
        }

        $key = "torob:proxy:cursor:{$group}";
        Cache::add($key, 0, now()->addDay());
        $cursor = max(0, (int) Cache::increment($key) - 1) % count($proxies);

        return array_merge(array_slice($proxies, $cursor), array_slice($proxies, 0, $cursor));
    }

    /** @return array{uri: string, id: string, source: string, uptime_percent: float, latency_ms: float} */
    private function endpoint(string $uri, string $source, float $uptime, float $latency): array
    {
        return [
            'uri' => $uri,
            'id' => $this->proxyId($uri),
            'source' => $source,
            'uptime_percent' => $uptime,
            'latency_ms' => $latency,
        ];
    }

    private function proxyId(string $uri): string
    {
        return substr(hash('sha256', $uri), 0, 16);
    }

    private function isQuarantined(string $id): bool
    {
        return Cache::has($this->quarantineKey($id));
    }

    private function leaseKey(string $id): string
    {
        return "torob:proxy:lease:{$id}";
    }

    private function quarantineKey(string $id): string
    {
        return "torob:proxy:quarantine:{$id}";
    }

    private function successKey(string $id): string
    {
        return "torob:proxy:success:{$id}";
    }
}
