<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class SetareganPriceFetcher
{
    /**
     * @return array{price: ?int, available: bool, source: ?string}
     */
    public static function fetchOffer(string $url, $logger = null): array
    {
        self::assertSetareganUrl($url);

        $cacheKey = 'setaregan:offer:'.sha1($url);

        return Cache::remember($cacheKey, now()->addSeconds(45), function () use ($url, $logger): array {
            if ($logger) {
                $logger->info("Fetching offer from setaregan.co: {$url}");
            }

            $html = self::fetchHtml($url, $logger);
            $priceResult = self::parsePrice($html, $logger);
            $availabilitySignal = self::parseAvailability($html);

            $price = $priceResult['price'] ?? null;
            $source = $priceResult['source'] ?? null;

            $available = match (true) {
                $availabilitySignal !== null => $availabilitySignal,
                $price !== null => true,
                default => false,
            };

            if ($logger) {
                $logger->info(sprintf(
                    'Setaregan offer resolved: price=%s available=%s source=%s',
                    $price === null ? 'null' : (string) $price,
                    $available ? 'true' : 'false',
                    $source ?? 'none',
                ));
            }

            return [
                'price' => $price,
                'available' => $available,
                'source' => $source,
            ];
        });
    }

    public static function fetchPrice(string $url, $logger = null): ?int
    {
        if ($logger) {
            $logger->info("Fetching price from setaregan.co: {$url}");
        }

        try {
            $offer = self::fetchOffer($url, $logger);

            if ($offer['price'] !== null && $logger) {
                $logger->info("Price found via HTML scraping: {$offer['price']}");
            } elseif ($logger) {
                $logger->warning('All methods failed to fetch price');
            }

            return $offer['price'];
        } catch (Throwable $exception) {
            if ($logger) {
                $logger->warning("Failed to fetch Setaregan price: {$exception->getMessage()}");
            }

            return null;
        }
    }

    public static function assertSetareganUrl(string $url): void
    {
        $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?? ''));

        if (! in_array($host, ['setaregan.co', 'www.setaregan.co'], true)) {
            throw new RuntimeException('The supplied URL is not a valid Setaregan product URL.');
        }
    }

    private static function fetchHtml(string $url, $logger = null): string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'fa-IR,fa;q=0.9,en-US;q=0.8,en;q=0.7',
                'Referer' => 'https://setaregan.co/',
            ])->timeout(20)->get($url);
        } catch (Throwable $exception) {
            throw new SetareganFetchException(
                "Setaregan request failed: {$exception->getMessage()}",
                previous: $exception,
            );
        }

        if (in_array($response->status(), [404, 410], true)) {
            throw new SetareganPageMissingException(
                "Setaregan product page was not found (HTTP {$response->status()})."
            );
        }

        if (! $response->successful()) {
            if ($logger) {
                $logger->warning("HTML request failed with status: {$response->status()}");
            }

            throw new SetareganFetchException(
                "Setaregan request failed with status {$response->status()}."
            );
        }

        return $response->body();
    }

    /**
     * @return array{price: ?int, source: ?string}
     */
    private static function parsePrice(string $html, $logger = null): array
    {
        $candidates = [];

        if (preg_match('/<meta[^>]*name=["\']product_price["\'][^>]*content=["\'](\d+)["\']/i', $html, $matches)) {
            $price = (int) $matches[1];
            if (self::isValidPrice($price)) {
                $candidates[] = ['price' => $price, 'priority' => 30, 'source' => 'meta-product_price'];
            }
        }

        if (preg_match_all('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.+?)<\/script>/s', $html, $scriptMatches)) {
            foreach ($scriptMatches[1] as $scriptContent) {
                $jsonData = json_decode($scriptContent, true);
                if ($jsonData && is_array($jsonData)) {
                    $paths = [
                        'offers.price',
                        'offers.0.price',
                        'price',
                        'aggregateOffer.lowPrice',
                        'aggregateOffer.highPrice',
                    ];

                    foreach ($paths as $path) {
                        $value = self::getNestedValue($jsonData, $path);
                        if ($value && is_numeric($value)) {
                            $price = (int) $value;
                            if (self::isValidPrice($price)) {
                                $candidates[] = ['price' => $price, 'priority' => 15, 'source' => 'json-ld'];
                            }
                        }
                    }
                }
            }
        }

        if (preg_match_all('/(\d{1,3}(?:[٬،,]\d{3})*)\s*تومان/u', $html, $matches)) {
            foreach ($matches[1] as $match) {
                $clean = str_replace([',', '،', '٬'], '', $match);
                if (is_numeric($clean)) {
                    $price = (int) $clean;
                    if (self::isValidPrice($price)) {
                        $candidates[] = ['price' => $price, 'priority' => 25, 'source' => 'toman-text'];
                    }
                }
            }
        }

        if (preg_match_all('/data-price=["\'](\d+)["\']/i', $html, $matches)) {
            foreach ($matches[1] as $match) {
                $price = (int) $match;
                if (self::isValidPrice($price)) {
                    $candidates[] = ['price' => $price, 'priority' => 20, 'source' => 'data-price'];
                }
            }
        }

        if (preg_match_all('/<(?:span|div|p)[^>]*class=["\'][^"\']*price[^"\']*["\'][^>]*>(.+?)<\/(?:span|div|p)>/is', $html, $matches)) {
            foreach ($matches[1] as $priceSection) {
                $priceText = strip_tags($priceSection);
                $price = self::parsePersianPrice($priceText);
                if ($price && self::isValidPrice($price)) {
                    $candidates[] = ['price' => $price, 'priority' => 18, 'source' => 'price-class'];
                }
            }
        }

        if (preg_match_all('/"price"\s*:\s*["\']?(\d+)["\']?/i', $html, $matches)) {
            foreach ($matches[1] as $match) {
                $price = (int) $match;
                if (self::isValidPrice($price)) {
                    $candidates[] = ['price' => $price, 'priority' => 10, 'source' => 'json-price'];
                }
            }
        }

        if ($candidates === []) {
            if ($logger) {
                $logger->warning('Could not find price in HTML content');
            }

            return ['price' => null, 'source' => null];
        }

        $unique = [];
        foreach ($candidates as $candidate) {
            $price = $candidate['price'];
            if (! isset($unique[$price]) || $unique[$price]['priority'] < $candidate['priority']) {
                $unique[$price] = $candidate;
            }
        }

        $unique = array_values($unique);

        usort($unique, function ($a, $b) {
            if ($a['priority'] === $b['priority']) {
                return $a['price'] <=> $b['price'];
            }

            return $b['priority'] <=> $a['priority'];
        });

        $best = $unique[0];

        if ($logger) {
            $logger->info("Selected price {$best['price']} from source {$best['source']}");
        }

        return [
            'price' => $best['price'],
            'source' => $best['source'],
        ];
    }

    private static function parseAvailability(string $html): ?bool
    {
        $section = self::mainProductSection($html);

        if (preg_match_all('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.+?)<\/script>/s', $html, $scriptMatches)) {
            foreach ($scriptMatches[1] as $scriptContent) {
                $jsonData = json_decode($scriptContent, true);
                if (! is_array($jsonData)) {
                    continue;
                }

                foreach (['offers.availability', 'offers.0.availability', 'availability'] as $path) {
                    $value = self::getNestedValue($jsonData, $path);
                    if (! is_string($value) || $value === '') {
                        continue;
                    }

                    $normalized = strtolower($value);
                    if (preg_match('/(?:outofstock|soldout|discontinued|backorder)/i', $normalized)) {
                        return false;
                    }
                    if (preg_match('/(?:instock|instoreonly|limitedavailability|preorder)/i', $normalized)) {
                        return true;
                    }
                }
            }
        }

        if (preg_match('/<meta[^>]*(?:property|name)=["\'](?:product:availability|availability)["\'][^>]*content=["\']([^"\']+)["\']/i', $html, $matches)
            || preg_match('/<meta[^>]*content=["\']([^"\']+)["\'][^>]*(?:property|name)=["\'](?:product:availability|availability)["\']/i', $html, $matches)) {
            $normalized = strtolower(trim($matches[1]));
            if (preg_match('/(?:oos|out[\s_-]*of[\s_-]*stock|outofstock)/i', $normalized)) {
                return false;
            }
            if (preg_match('/(?:in[\s_-]*stock|instock)/i', $normalized)) {
                return true;
            }
        }

        $normalizedSection = self::normalizePersianText($section);

        $outOfStockPhrases = [
            'ناموجود',
            'موجود نیست',
            'عدم موجودی',
            'اتمام موجودی',
            'تمام شد',
            'اطلاع از موجودی',
            'تماس بگیرید',
            'به زودی',
        ];

        foreach ($outOfStockPhrases as $phrase) {
            if (str_contains($normalizedSection, self::normalizePersianText($phrase))) {
                return false;
            }
        }

        if (preg_match('/<(?:button|a|input)[^>]*(?:disabled|aria-disabled=["\']true["\'])[^>]*(?:افزودن به سبد|خرید|add[\s_-]?to[\s_-]?cart)[^>]*>/iu', $section)
            || preg_match('/<(?:button|a|input)[^>]*(?:افزودن به سبد|خرید|add[\s_-]?to[\s_-]?cart)[^>]*(?:disabled|aria-disabled=["\']true["\'])[^>]*>/iu', $section)) {
            return false;
        }

        if (preg_match('/<(?:button|a)[^>]*>[^<]*(?:افزودن به سبد(?:\s*خرید)?|خرید)[^<]*<\/(?:button|a)>/iu', $section)
            || preg_match('/add[\s_-]?to[\s_-]?cart/i', $section)) {
            return true;
        }

        return null;
    }

    private static function mainProductSection(string $html): string
    {
        $start = stripos($html, '<h1');
        if ($start === false) {
            return $html;
        }

        $slice = substr($html, $start);
        if (preg_match('/محصولات مشابه|محصولات مرتبط|کالاهای مشابه|نظرات کاربران|دیدگاه/u', $slice, $matches, PREG_OFFSET_CAPTURE)) {
            return substr($slice, 0, $matches[0][1]);
        }

        return $slice;
    }

    private static function normalizePersianText(string $text): string
    {
        $normalized = str_replace(
            ["\u{064A}", "\u{0643}", "\u{200C}", "\u{200D}"],
            ["\u{06CC}", "\u{06A9}", ' ', ' '],
            $text,
        );
        $normalized = preg_replace('/\s+/u', ' ', trim($normalized)) ?? trim($normalized);

        return mb_strtolower($normalized, 'UTF-8');
    }

    private static function parsePersianPrice(string $priceText): ?int
    {
        $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $normalized = str_replace($persianDigits, $englishDigits, $priceText);
        $normalized = preg_replace('/[تومان|ریال|Toman|Rial|\$|€|£]/ui', '', $normalized);
        $normalized = preg_replace('/[,\s٬،]/u', '', $normalized);

        if (preg_match('/(\d+)/', $normalized, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private static function isValidPrice(int $price): bool
    {
        return $price >= 1000 && $price <= 100000000;
    }

    private static function getNestedValue(array $array, string $path): mixed
    {
        $keys = explode('.', $path);
        $value = $array;

        foreach ($keys as $key) {
            if (! isset($value[$key])) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }
}
