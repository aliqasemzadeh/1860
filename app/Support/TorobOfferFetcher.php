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
    public function cheapestCompetitor(
        string $productUrl,
        array $excludedShopNames,
        ?int $minPrice = null,
        int $stepAmount = 0,
    ): ?array {
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

        $rankedOffers = $eligible
            ->map(fn (array $offer, int $index): array => [
                'rank' => $index + 1,
                'shop_name' => (string) $offer['shop_name'],
                'price' => $this->numericPrice($offer['price']) ?? 0,
            ])
            ->all();

        $logContext = [
            'source' => 'torob',
            'product_key' => $productKey,
            'product_url' => $productUrl,
            'eligible_count' => count($rankedOffers),
            'ranking_summary' => $this->rankingSummary($rankedOffers),
            'offers' => $rankedOffers,
            'min_price' => $minPrice,
            'step_amount' => $stepAmount,
        ];

        if ($eligible->isEmpty()) {
            FetcherLog::info('Torob: لیست قیمت رقبا', $logContext);

            return null;
        }

        $selection = $this->selectCompetitor($eligible, $minPrice, $stepAmount);
        $offer = $selection['offer'];
        $selected = [
            'price' => $this->numericPrice($offer['price']) ?? 0,
            'shop_name' => (string) $offer['shop_name'],
            'offer_key' => isset($offer['prk']) ? (string) $offer['prk'] : null,
        ];

        FetcherLog::info('Torob: لیست قیمت رقبا', array_merge($logContext, [
            'selected_shop' => $selected['shop_name'],
            'selected_price' => $selected['price'],
            'selected_rank' => $selection['rank'],
            'selection_reason' => $selection['reason'],
            'skipped_below_floor' => $selection['skipped'],
        ]));

        return $selected;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $eligible
     * @return array{
     *     offer: array<string, mixed>,
     *     rank: int,
     *     reason: string,
     *     skipped: list<array{rank: int, shop_name: string, price: int, candidate_price: int}>
     * }
     */
    private function selectCompetitor($eligible, ?int $minPrice, int $stepAmount): array
    {
        $skipped = [];

        if ($minPrice === null) {
            return [
                'offer' => $eligible->first(),
                'rank' => 1,
                'reason' => 'first_eligible',
                'skipped' => $skipped,
            ];
        }

        foreach ($eligible as $index => $offer) {
            $rank = $index + 1;
            $price = $this->numericPrice($offer['price']) ?? 0;
            $candidate = $price - $stepAmount;

            if ($candidate >= $minPrice) {
                return [
                    'offer' => $offer,
                    'rank' => $rank,
                    'reason' => $skipped === []
                        ? 'first_eligible'
                        : 'skipped_'.count($skipped).'_below_floor',
                    'skipped' => $skipped,
                ];
            }

            $skipped[] = [
                'rank' => $rank,
                'shop_name' => (string) $offer['shop_name'],
                'price' => $price,
                'candidate_price' => $candidate,
            ];
        }

        return [
            'offer' => $eligible->first(),
            'rank' => 1,
            'reason' => 'all_below_floor_fallback',
            'skipped' => $skipped,
        ];
    }

    /**
     * @param  list<array{rank: int, shop_name: string, price: int}>  $rankedOffers
     */
    private function rankingSummary(array $rankedOffers): string
    {
        if ($rankedOffers === []) {
            return 'هیچ رقیب واجدی پیدا نشد.';
        }

        return collect($rankedOffers)
            ->map(fn (array $offer): string => sprintf(
                'نفر %d: %s — %s',
                $offer['rank'],
                $offer['shop_name'],
                number_format($offer['price']),
            ))
            ->implode(' | ');
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
