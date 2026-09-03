<?php

namespace App\Scrapers;

use App\Scrapers\Runners\ProcessPuppeteerRunner;
use EduLazaro\Larascraper\Runners\HttpRunner;
use EduLazaro\Larascraper\Scraper;
use JsonException;
use RuntimeException;
use Symfony\Component\DomCrawler\Crawler;

class TorobScraper extends Scraper
{
    private const SELLERS_ENDPOINT = 'https://api.torob.com/v4/base-product/sellers/';

    private const PAGE_SIZE = 100;

    private const MAX_PAGES = 10;

    protected array $drivers = [
        'browser' => ProcessPuppeteerRunner::class,
        'http' => HttpRunner::class,
    ];

    protected int $timeout = 20_000;

    protected int $tries = 2;

    protected int $retryDelay = 2;

    protected array $headers = [
        'Accept' => 'application/json',
        'Accept-Language' => 'fa-IR,fa;q=0.9,en;q=0.8',
        'Origin' => 'https://torob.com',
        'Referer' => 'https://torob.com/',
    ];

    /** @return list<array<string, mixed>> */
    protected function handle(string $productKey): array
    {
        $offers = [];
        $page = 0;
        $pageCount = 1;

        while ($page < $pageCount && $page < self::MAX_PAGES) {
            $response = $this->scrape($this->sellersUrl($productKey, $page))->run();
            $payload = $this->decodePayload((string) $response->data);

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

    private function sellersUrl(string $productKey, int $page): string
    {
        return self::SELLERS_ENDPOINT.'?'.http_build_query([
            'source' => 'next_desktop',
            'prk' => $productKey,
            'page' => $page,
            'size' => self::PAGE_SIZE,
        ]);
    }

    /** @return array{count?: mixed, results: array<int, mixed>} */
    private function decodePayload(string $content): array
    {
        $json = trim($content);

        if (str_starts_with($json, '<')) {
            $crawler = new Crawler($json);
            $pre = $crawler->filter('pre');
            $json = $pre->count() > 0 ? trim($pre->first()->text('')) : '';
        }

        try {
            $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Torob sellers response did not contain valid JSON.', previous: $exception);
        }

        if (! is_array($payload) || ! isset($payload['results']) || ! is_array($payload['results'])) {
            throw new RuntimeException('Torob sellers response has an unexpected structure.');
        }

        return $payload;
    }
}
