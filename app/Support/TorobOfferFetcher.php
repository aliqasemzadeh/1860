<?php

namespace App\Support;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use Throwable;

class TorobOfferFetcher
{
    private const SELLERS_ENDPOINT = 'https://api.torob.com/v4/base-product/sellers/';

    private const PAGE_SIZE = 100;

    private const MAX_PAGES = 10;

    private const BLOCKED_CACHE_KEY = 'torob:sellers:blocked-until';

    private const CURL_STATUS_MARKER = '__TOROB_HTTP_STATUS__:';

    private const BROWSER_USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36';

    /**
     * @param  list<string>  $excludedShopNames
     * @return array{price: int, shop_name: string, offer_key: ?string}|null
     */
    public function cheapestCompetitor(string $productUrl, array $excludedShopNames): ?array
    {
        $productKey = $this->extractProductKey($productUrl);
        $offers = Cache::remember(
            "torob:offers:{$productKey}",
            now()->addSeconds(45),
            fn (): array => $this->fetchOffers($productKey),
        );

        $excluded = collect($excludedShopNames)
            ->filter(fn ($name): bool => is_string($name) && trim($name) !== '')
            ->map(fn (string $name): string => $this->normalizeShopName($name))
            ->unique()
            ->all();

        $eligible = collect($offers)
            ->filter(function (array $offer) use ($excluded): bool {
                $price = $this->numericPrice($offer['price'] ?? null);
                $shopName = (string) ($offer['shop_name'] ?? '');

                return ($offer['availability'] ?? false) === true
                    && $price !== null
                    && $price > 0
                    && $shopName !== ''
                    && ! in_array($this->normalizeShopName($shopName), $excluded, true)
                    && ! $this->isInstallmentOnly($offer);
            })
            ->sortBy(fn (array $offer): int => $this->numericPrice($offer['price']) ?? PHP_INT_MAX)
            ->values();

        if ($eligible->isEmpty()) {
            return null;
        }

        $offer = $eligible->first();

        return [
            'price' => $this->numericPrice($offer['price']) ?? 0,
            'shop_name' => (string) $offer['shop_name'],
            'offer_key' => isset($offer['prk']) ? (string) $offer['prk'] : null,
        ];
    }

    public function extractProductKey(string $url): string
    {
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = (string) ($parts['path'] ?? '');

        if (! in_array($host, ['torob.com', 'www.torob.com'], true)
            || ! preg_match('~^/p/([0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12})(?:/|$)~i', $path, $matches)) {
            throw new RuntimeException('The supplied URL is not a valid Torob product URL.');
        }

        return strtolower($matches[1]);
    }

    /** @return list<array<string, mixed>> */
    private function fetchOffers(string $productKey): array
    {
        $this->ensureRequestsAreNotBlocked();

        $offers = [];
        $page = 0;
        $pageCount = 1;
        $cookieJar = new CookieJar;
        $client = $this->client($cookieJar);
        $sessionPrimed = false;

        while ($page < $pageCount && $page < self::MAX_PAGES) {
            $response = $this->requestOffersPage($client, $productKey, $page);

            if ($response->status() === 490 && ! $sessionPrimed) {
                $this->primeBrowserSession($cookieJar, $productKey);
                $client = $this->client($cookieJar);
                $sessionPrimed = true;
                $response = $this->requestOffersPage($client, $productKey, $page);
            }

            if ($response->status() === 490) {
                $response = $this->requestOffersPageWithSystemCurl($productKey, $page);
            }

            if (! $response->successful()) {
                if ($response->status() === 490) {
                    $this->blockFurtherRequests();

                    throw new RuntimeException('Torob temporarily rejected the sellers request (HTTP 490); the current price was not changed.');
                }

                throw new RuntimeException("Torob sellers endpoint returned HTTP {$response->status()}.");
            }

            $payload = $response->json();
            if (! is_array($payload) || ! isset($payload['results']) || ! is_array($payload['results'])) {
                throw new RuntimeException('Torob sellers response has an unexpected structure.');
            }

            foreach ($payload['results'] as $offer) {
                if (is_array($offer)) {
                    $offers[] = $offer;
                }
            }

            $count = max(0, (int) ($payload['count'] ?? count($offers)));
            $pageCount = max(1, (int) ceil($count / self::PAGE_SIZE));
            $page++;
        }

        return $offers;
    }

    private function ensureRequestsAreNotBlocked(): void
    {
        $blockedUntil = (int) Cache::get(self::BLOCKED_CACHE_KEY, 0);

        if ($blockedUntil > now()->timestamp) {
            throw new RuntimeException(
                'Torob sellers requests are cooling down after HTTP 490 until '.date('Y-m-d H:i:s', $blockedUntil).'; the current price was not changed.',
            );
        }

        Cache::forget(self::BLOCKED_CACHE_KEY);
    }

    private function blockFurtherRequests(): void
    {
        $seconds = max(60, (int) config('services.torob.block_cooldown_seconds', 600));
        $blockedUntil = now()->addSeconds($seconds);

        Cache::put(self::BLOCKED_CACHE_KEY, $blockedUntil->timestamp, $blockedUntil);
    }

    private function requestOffersPage(PendingRequest $client, string $productKey, int $page): Response
    {
        return $client->get(self::SELLERS_ENDPOINT, [
            'source' => 'next_desktop',
            'prk' => $productKey,
            'page' => $page,
            'size' => self::PAGE_SIZE,
        ]);
    }

    private function primeBrowserSession(CookieJar $cookieJar, string $productKey): void
    {
        Http::withOptions(['cookies' => $cookieJar])
            ->withHeaders($this->browserHeaders())
            ->accept('text/html,application/xhtml+xml')
            ->timeout(10)
            ->connectTimeout(5)
            ->get("https://torob.com/p/{$productKey}/");
    }

    private function requestOffersPageWithSystemCurl(string $productKey, int $page): Response
    {
        $query = http_build_query([
            'source' => 'next_desktop',
            'prk' => $productKey,
            'page' => $page,
            'size' => self::PAGE_SIZE,
        ]);
        $binary = (string) config('services.torob.curl_binary', 'curl');

        $result = Process::timeout(20)->run([
            $binary,
            '--silent',
            '--show-error',
            '--compressed',
            '--max-time',
            '15',
            '--connect-timeout',
            '5',
            '--header',
            'Accept: application/json',
            '--header',
            'User-Agent: '.self::BROWSER_USER_AGENT,
            '--header',
            'Referer: https://torob.com/',
            '--header',
            'Origin: https://torob.com',
            '--header',
            'Accept-Language: fa-IR,fa;q=0.9,en;q=0.8',
            '--write-out',
            '\n'.self::CURL_STATUS_MARKER.'%{http_code}',
            self::SELLERS_ENDPOINT.'?'.$query,
        ]);

        $output = $result->output();
        $statusSeparator = strrpos($output, self::CURL_STATUS_MARKER);

        if ($statusSeparator === false) {
            $error = trim($result->errorOutput());
            $details = $error !== '' ? mb_substr($error, 0, 500) : 'no error output';

            throw new RuntimeException(
                "Torob sellers fallback transport returned an invalid response (exit {$result->exitCode()}: {$details}).",
            );
        }

        $body = rtrim(substr($output, 0, $statusSeparator), "\r\n");
        $status = (int) trim(substr($output, $statusSeparator + strlen(self::CURL_STATUS_MARKER)));

        if ($status < 100 || $status > 599) {
            $error = trim($result->errorOutput());
            throw new RuntimeException('Torob sellers fallback transport failed'.($error !== '' ? ": {$error}" : '.'));
        }

        return new Response(new Psr7Response(
            $status,
            ['Content-Type' => 'application/json'],
            $body,
        ));
    }

    private function client(CookieJar $cookieJar): PendingRequest
    {
        return Http::withOptions(['cookies' => $cookieJar])
            ->withHeaders($this->browserHeaders())
            ->acceptJson()
            ->timeout(10)
            ->connectTimeout(5)
            ->retry(
                [400, 1000],
                when: fn (Throwable $exception): bool => $exception instanceof \Illuminate\Http\Client\ConnectionException
                    || ($exception instanceof \Illuminate\Http\Client\RequestException
                        && ($exception->response->status() === 429 || $exception->response->serverError())),
                throw: false,
            );
    }

    /** @return array<string, string> */
    private function browserHeaders(): array
    {
        return [
            'User-Agent' => self::BROWSER_USER_AGENT,
            'Referer' => 'https://torob.com/',
            'Origin' => 'https://torob.com',
            'Accept-Language' => 'fa-IR,fa;q=0.9,en;q=0.8',
        ];
    }

    private function normalizeShopName(string $name): string
    {
        $normalized = str_replace(
            ["\u{064A}", "\u{0643}", "\u{200C}", "\u{200D}"],
            ["\u{06CC}", "\u{06A9}", ' ', ' '],
            $name,
        );
        $normalized = preg_replace('/\s+/u', ' ', trim($normalized)) ?? trim($normalized);

        return mb_strtolower($normalized, 'UTF-8');
    }

    private function numericPrice(mixed $price): ?int
    {
        if (is_int($price)) {
            return $price;
        }

        if (is_float($price) || (is_string($price) && ctype_digit($price))) {
            return (int) $price;
        }

        return null;
    }

    /** @param array<string, mixed> $offer */
    private function isInstallmentOnly(array $offer): bool
    {
        $mode = strtolower((string) ($offer['price_text_mode'] ?? ''));
        $buttonText = (string) ($offer['button_text'] ?? '');

        return in_array($mode, ['installment', 'bnpl'], true)
            || str_contains($buttonText, 'قسطی');
    }
}
