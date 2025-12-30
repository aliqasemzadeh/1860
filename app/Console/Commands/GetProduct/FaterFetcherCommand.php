<?php

namespace App\Console\Commands\GetProduct;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FaterFetcherCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fater-fetcher {url : Product page or API URL on faterco.ir}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and display image URLs from a Fater product page swiper or product API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url');

        // If it's a product page URL, extract slug and build API URL
        $apiUrl = $this->buildApiUrl($url);
        
        if ($apiUrl !== $url) {
            $this->info("Product page detected: {$url}");
            $this->info("Fetching from API: {$apiUrl}");
        } else {
            $this->info("Fetching from: {$url}");
        }

        try {
            $response = Http::get($apiUrl);
        } catch (\Throwable $e) {
            $this->error('Request failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        if (! $response->ok()) {
            $this->error('Request failed with status code: ' . $response->status());
            return self::FAILURE;
        }

        $imageUrls = [];
        $contentType = $response->header('Content-Type', '');

        // If API returns JSON (e.g. https://api.faterco.ir/api/v1/Product/GetProductDetail?...),
        // extract image URLs from JSON payload.
        if (str_contains($contentType, 'application/json')) {
            $json = $response->json();

            if (! is_array($json)) {
                $this->error('Invalid JSON response structure.');
                return self::FAILURE;
            }

            $this->info('Detected JSON response from API. Extracting image URLs from JSON...');
            $imageUrls = $this->extractImageUrlsFromApi($json);
        } else {
            // Fallback: treat as HTML product page and extract from swiper
            $html = $response->body();
            $this->info('Detected HTML response. Extracting image URLs from swiper...');
            $imageUrls = $this->extractImageUrls($html, $url);
        }

        if (empty($imageUrls)) {
            $this->warn('No images were found.');
            return self::SUCCESS;
        }

        $this->info('Found image URLs:');
        foreach ($imageUrls as $imgUrl) {
            $this->line($imgUrl);
        }

        return self::SUCCESS;
    }

    /**
     * Build API URL from product page URL or return original URL if it's already an API URL.
     * 
     * @param  string  $url
     * @return string
     */
    protected function buildApiUrl(string $url): string
    {
        // If it's already an API URL, return as is
        if (str_contains($url, 'api.faterco.ir/api/v1/Product/GetProductDetail')) {
            return $url;
        }

        // Extract product slug from product page URL
        // Pattern: https://faterco.ir/product/{slug}
        if (preg_match('#faterco\.ir/product/([^/?]+)#i', $url, $matches)) {
            $slug = $matches[1];
            return "https://api.faterco.ir/api/v1/Product/GetProductDetail?id={$slug}";
        }

        // If pattern doesn't match, return original URL
        return $url;
    }

    /**
     * Extract image URLs from HTML content.
     * Only extracts images from swiper containers:
     * - swiper, product-swiper-main
     * - swiper-wrapper, swiper-slide
     *
     * @param  string  $html
     * @param  string  $baseUrl
     * @return array<int, string>
     */
    protected function extractImageUrls(string $html, string $baseUrl): array
    {
        $dom = new \DOMDocument();

        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        
        // XPath queries for swiper containers
        $queries = [
            "//*[contains(@class, 'swiper') and contains(@class, 'product-swiper-main')]//img[@src]",
            "//*[contains(@class, 'swiper-wrapper')]//img[@src]",
            "//*[contains(@class, 'swiper-slide')]//img[@src]",
            "//*[contains(@class, 'swiper')]//img[@src]",
        ];

        $urls = [];

        foreach ($queries as $query) {
            $nodes = $xpath->query($query);
            
            if (! $nodes) {
                continue;
            }

            foreach ($nodes as $node) {
                /** @var \DOMElement $node */
                $src = trim($node->getAttribute('src'));
                if ($src === '') {
                    continue;
                }

                // Also check data-src for lazy-loaded images
                if (empty($src) || $src === 'data:image') {
                    $src = trim($node->getAttribute('data-src') ?? '');
                }

                // Check data-lazy-src for swiper lazy loading
                if (empty($src) || $src === 'data:image') {
                    $src = trim($node->getAttribute('data-lazy-src') ?? '');
                }

                if ($src === '') {
                    continue;
                }

                $absolute = $this->toAbsoluteUrl($src, $baseUrl);

                if (! in_array($absolute, $urls, true)) {
                    $urls[] = $absolute;
                }
            }
        }

        return $urls;
    }

    /**
     * Extract image URLs from Fater product API JSON response.
     * Works generically by scanning all string fields for image-like URLs.
     *
     * @param  array<mixed>  $data
     * @return array<int, string>
     */
    protected function extractImageUrlsFromApi(array $data): array
    {
        $urls = [];

        $this->collectImageUrlsFromValue($data, $urls);

        // Make unique and reindex
        $urls = array_values(array_unique($urls));

        return $urls;
    }

    /**
     * Recursively walk the JSON structure and collect image URLs.
     *
     * @param  mixed  $value
     * @param  array<int, string>  $urls
     */
    protected function collectImageUrlsFromValue(mixed $value, array &$urls): void
    {
        if (is_array($value)) {
            foreach ($value as $v) {
                $this->collectImageUrlsFromValue($v, $urls);
            }

            return;
        }

        if (! is_string($value)) {
            return;
        }

        // Only keep strings that look like image URLs
        if (! $this->looksLikeImageUrl($value)) {
            return;
        }

        $normalized = $this->normalizeFaterImageUrl($value);

        if (! in_array($normalized, $urls, true)) {
            $urls[] = $normalized;
        }
    }

    /**
     * Check if a string looks like an image URL (jpg, jpeg, png, webp, gif).
     */
    protected function looksLikeImageUrl(string $value): bool
    {
        return (bool) preg_match('#\.(jpe?g|png|webp|gif)(\?|$)#i', $value);
    }

    /**
     * Normalize image URLs from Fater:
     * - If already absolute, return as is
     * - If protocol-relative (//...), prefix https:
     * - If path-like starting with /images, prefix https://admin.faterco.ir
     */
    protected function normalizeFaterImageUrl(string $value): string
    {
        $trimmed = trim($value);

        // Absolute URL
        if (preg_match('#^https?://#i', $trimmed)) {
            return $trimmed;
        }

        // Protocol-relative URL
        if (strpos($trimmed, '//') === 0) {
            return 'https:' . $trimmed;
        }

        // URLs starting with /images -> admin.faterco.ir
        if (strpos($trimmed, '/images') === 0) {
            return 'https://admin.faterco.ir' . $trimmed;
        }

        return $trimmed;
    }

    /**
     * Convert a possibly relative URL to an absolute URL using a base URL.
     */
    protected function toAbsoluteUrl(string $src, string $baseUrl): string
    {
        // Already absolute
        if (preg_match('#^https?://#i', $src)) {
            return $src;
        }

        $base = parse_url($baseUrl);
        if (! $base || ! isset($base['scheme'], $base['host'])) {
            return $src;
        }

        $scheme = $base['scheme'];
        $host = $base['host'];
        $port = isset($base['port']) ? ':' . $base['port'] : '';

        // If src starts with //, keep scheme
        if (strpos($src, '//') === 0) {
            return $scheme . ':' . $src;
        }

        // If src starts with /, it's relative to domain root
        if (strpos($src, '/') === 0) {
            return "{$scheme}://{$host}{$port}{$src}";
        }

        // Otherwise, relative to current path
        $path = $base['path'] ?? '/';
        // Remove filename if present
        if (substr($path, -1) !== '/') {
            $path = dirname($path) . '/';
        }

        return "{$scheme}://{$host}{$port}" . $path . $src;
    }
}
