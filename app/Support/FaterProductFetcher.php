<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

class FaterProductFetcher
{
    /**
     * Fetch product information from faterco.ir URL
     *
     * @return array{name: string|null, description: string|null, slug: string|null, slug_fa: string|null, weight: float|null, x_dimension: float|null, y_dimension: float|null, z_dimension: float|null}
     */
    public static function fetchProductInfo(string $url, $logger = null): array
    {
        if ($logger) {
            $logger->info("Fetching product info from faterco.ir: {$url}");
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'fa-IR,fa;q=0.9,en-US;q=0.8,en;q=0.7',
                'Referer' => 'https://faterco.ir/',
            ])->timeout(15)->get($url);

            if ($response->successful()) {
                $html = $response->body();

                $productInfo = [
                    'name' => self::extractName($html),
                    'description' => self::extractDescription($html),
                    'slug' => self::extractSlug($url),
                    'slug_fa' => self::extractSlugFa($url),
                    'weight' => self::extractWeight($html),
                    'x_dimension' => self::extractDimension($html, 'x'),
                    'y_dimension' => self::extractDimension($html, 'y'),
                    'z_dimension' => self::extractDimension($html, 'z'),
                ];

                if ($logger) {
                    $logger->info('Product info extracted: '.json_encode($productInfo));
                }

                return $productInfo;
            } else {
                if ($logger) {
                    $logger->warn("Request failed with status: {$response->status()}");
                }
            }
        } catch (\Exception $e) {
            if ($logger) {
                $logger->warn("Exception: {$e->getMessage()}");
            }
        }

        return [
            'name' => null,
            'description' => null,
            'slug' => null,
            'slug_fa' => null,
            'weight' => null,
            'x_dimension' => null,
            'y_dimension' => null,
            'z_dimension' => null,
        ];
    }

    private static function extractName(string $html): ?string
    {
        // Try multiple methods to extract product name

        // Method 1: Meta tag og:title
        if (preg_match('/<meta[^>]*property=["\']og:title["\'][^>]*content=["\']([^"\']+)["\']/', $html, $matches)) {
            return trim($matches[1]);
        }

        // Method 2: Meta tag title
        if (preg_match('/<meta[^>]*name=["\']title["\'][^>]*content=["\']([^"\']+)["\']/', $html, $matches)) {
            return trim($matches[1]);
        }

        // Method 3: H1 tag
        if (preg_match('/<h1[^>]*>(.+?)<\/h1>/s', $html, $matches)) {
            $name = strip_tags($matches[1]);
            $name = html_entity_decode($name, ENT_QUOTES, 'UTF-8');

            return trim($name);
        }

        // Method 4: Title tag
        if (preg_match('/<title[^>]*>(.+?)<\/title>/s', $html, $matches)) {
            $name = strip_tags($matches[1]);
            $name = html_entity_decode($name, ENT_QUOTES, 'UTF-8');
            // Remove common suffixes
            $name = preg_replace('/\s*[-|]\s*.*$/', '', $name);

            return trim($name);
        }

        return null;
    }

    private static function extractDescription(string $html): ?string
    {
        // Method 1: Meta description
        if (preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\']([^"\']+)["\']/', $html, $matches)) {
            return trim($matches[1]);
        }

        // Method 2: Meta og:description
        if (preg_match('/<meta[^>]*property=["\']og:description["\'][^>]*content=["\']([^"\']+)["\']/', $html, $matches)) {
            return trim($matches[1]);
        }

        // Method 3: Look for description in content
        if (preg_match('/<div[^>]*class=["\'][^"\']*description[^"\']*["\'][^>]*>(.+?)<\/div>/s', $html, $matches)) {
            $description = strip_tags($matches[1]);
            $description = html_entity_decode($description, ENT_QUOTES, 'UTF-8');
            $description = preg_replace('/\s+/', ' ', $description);

            return trim($description);
        }

        return null;
    }

    private static function extractSlug(string $url): ?string
    {
        // Extract slug from URL: https://faterco.ir/product/fater-kcr-8000b-mechanical-keyboard
        if (preg_match('/\/product\/([^\/]+)/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private static function extractSlugFa(string $url): ?string
    {
        // For now, use the same as slug
        return self::extractSlug($url);
    }

    private static function extractWeight(string $html): ?float
    {
        // Look for weight information in the HTML
        // Pattern: weight, وزن, etc.
        $patterns = [
            '/وزن[:\s]+(\d+(?:\.\d+)?)\s*(?:گرم|g|kg|کیلوگرم)/ui',
            '/weight[:\s]+(\d+(?:\.\d+)?)\s*(?:g|kg|gram|kilogram)/ui',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $weight = (float) $matches[1];
                // Convert kg to grams if needed
                if (stripos($matches[0], 'kg') !== false || stripos($matches[0], 'کیلوگرم') !== false) {
                    $weight *= 1000;
                }

                return $weight;
            }
        }

        return null;
    }

    private static function extractDimension(string $html, string $axis): ?float
    {
        // Look for dimensions in the HTML
        // Patterns: طول, عرض, ارتفاع, length, width, height
        $patterns = [
            'x' => [
                '/طول[:\s]+(\d+(?:\.\d+)?)\s*(?:mm|cm|سانتیمتر|میلی‌متر)/ui',
                '/length[:\s]+(\d+(?:\.\d+)?)\s*(?:mm|cm)/ui',
            ],
            'y' => [
                '/عرض[:\s]+(\d+(?:\.\d+)?)\s*(?:mm|cm|سانتیمتر|میلی‌متر)/ui',
                '/width[:\s]+(\d+(?:\.\d+)?)\s*(?:mm|cm)/ui',
            ],
            'z' => [
                '/ارتفاع[:\s]+(\d+(?:\.\d+)?)\s*(?:mm|cm|سانتیمتر|میلی‌متر)/ui',
                '/height[:\s]+(\d+(?:\.\d+)?)\s*(?:mm|cm)/ui',
            ],
        ];

        if (! isset($patterns[$axis])) {
            return null;
        }

        foreach ($patterns[$axis] as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $dimension = (float) $matches[1];
                // Convert cm to mm if needed
                if (stripos($matches[0], 'cm') !== false || stripos($matches[0], 'سانتیمتر') !== false) {
                    $dimension *= 10;
                }

                return $dimension;
            }
        }

        return null;
    }
}
