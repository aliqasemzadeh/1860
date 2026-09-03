<?php

namespace App\Support;

use App\Scrapers\TorobScraper;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class TorobOfferFetcher
{
    public function __construct(
        private readonly TorobScraper $scraper,
        private readonly TorobChallengeGuard $challengeGuard,
    ) {}

    /**
     * @param  list<string>  $excludedShopNames
     * @return array{price: int, shop_name: string, offer_key: ?string}|null
     */
    public function cheapestCompetitor(string $productUrl, array $excludedShopNames): ?array
    {
        $this->challengeGuard->ensureRequestsAllowed();

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
        $response = $this->scraper->handleToResponse([$productKey]);

        if (! $response->success || ! is_array($response->data)) {
            throw new RuntimeException($response->error ?? 'Torob scraper returned an unexpected response.');
        }

        return array_values(array_filter($response->data, 'is_array'));
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
