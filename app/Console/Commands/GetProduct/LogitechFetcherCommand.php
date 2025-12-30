<?php

namespace App\Console\Commands\GetProduct;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class LogitechFetcherCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:logitech-fetcher {url : Product page URL on logitech.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and display image URLs from a Logitech product page';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url');

        $this->info("Fetching HTML from: {$url}");

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept-Encoding' => 'gzip, deflate',
                'Referer' => 'https://www.logitech.com/',
                'Sec-Fetch-Dest' => 'document',
                'Sec-Fetch-Mode' => 'navigate',
                'Sec-Fetch-Site' => 'same-origin',
                'Sec-Fetch-User' => '?1',
                'Upgrade-Insecure-Requests' => '1',
            ])
            ->timeout(30)
            ->retry(2, 1000)
            ->get($url);
        } catch (\Throwable $e) {
            $this->error('Request failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        if (! $response->ok()) {
            $this->error('Request failed with status code: ' . $response->status());
            $this->warn('The website may be blocking automated requests.');
            return self::FAILURE;
        }

        $html = $response->body();

        // Try to find API endpoint or JSON data first
        $imageUrls = $this->extractFromJson($html, $url);

        // If no JSON found, extract from HTML
        if (empty($imageUrls)) {
            $imageUrls = $this->extractImageUrls($html, $url);
        }

        // Filter to only product images (exclude logos, icons, etc.)
        $productImages = $this->filterProductImages($imageUrls);

        // Clean up Logitech CDN URLs - extract actual image paths
        $cleanImages = $this->cleanLogitechUrls($productImages);

        if (empty($cleanImages)) {
            $this->warn('No product images were found.');
            if (! empty($imageUrls)) {
                $this->info('Found ' . count($imageUrls) . ' total images, but none matched product pattern.');
                $this->info('All found URLs (for debugging):');
                foreach ($imageUrls as $imgUrl) {
                    $this->line($imgUrl);
                }
            }
            return self::SUCCESS;
        }

        $this->info('Found product image URLs:');
        foreach ($cleanImages as $imgUrl) {
            $this->line($imgUrl);
        }

        return self::SUCCESS;
    }

    /**
     * Extract image URLs from JSON data in script tags.
     *
     * @param  string  $html
     * @param  string  $baseUrl
     * @return array<int, string>
     */
    protected function extractFromJson(string $html, string $baseUrl): array
    {
        $urls = [];

        // Look for JSON-LD or inline JSON with image data
        if (preg_match_all('#<script[^>]*type=["\']application/ld\+json["\'][^>]*>(.*?)</script>#is', $html, $matches)) {
            foreach ($matches[1] as $jsonContent) {
                $data = json_decode($jsonContent, true);
                if (is_array($data)) {
                    $this->collectImagesFromJson($data, $urls);
                }
            }
        }

        // Look for product data in JavaScript variables
        if (preg_match_all('#(?:product|images?|gallery)\s*[:=]\s*(\{.*?\})#is', $html, $jsMatches)) {
            foreach ($jsMatches[1] as $jsJson) {
                // Try to extract valid JSON
                if (preg_match('#\{.*\}s#is', $jsJson, $jsonMatch)) {
                    $data = json_decode($jsonMatch[0], true);
                    if (is_array($data)) {
                        $this->collectImagesFromJson($data, $urls);
                    }
                }
            }
        }

        // Look for image URLs in script tags (generic pattern)
        if (preg_match_all('#<script[^>]*>(.*?)</script>#is', $html, $scriptMatches)) {
            foreach ($scriptMatches[1] as $scriptContent) {
                // Find URLs that look like images from logitech.com
                if (preg_match_all('#["\'](https?://[^"\']*logitech[^"\']*\.(?:jpe?g|png|webp|gif)[^"\']*)["\']#i', $scriptContent, $imgMatches)) {
                    foreach ($imgMatches[1] as $imgUrl) {
                        if (! in_array($imgUrl, $urls, true)) {
                            $urls[] = $imgUrl;
                        }
                    }
                }
            }
        }

        return array_values(array_unique($urls));
    }

    /**
     * Recursively collect image URLs from JSON structure.
     *
     * @param  mixed  $data
     * @param  array<int, string>  $urls
     */
    protected function collectImagesFromJson(mixed $data, array &$urls): void
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_string($key) && (str_contains(strtolower($key), 'image') || str_contains(strtolower($key), 'picture') || str_contains(strtolower($key), 'photo'))) {
                    if (is_string($value) && $this->looksLikeImageUrl($value)) {
                        if (! in_array($value, $urls, true)) {
                            $urls[] = $value;
                        }
                    } elseif (is_array($value)) {
                        foreach ($value as $item) {
                            if (is_string($item) && $this->looksLikeImageUrl($item)) {
                                if (! in_array($item, $urls, true)) {
                                    $urls[] = $item;
                                }
                            }
                        }
                    }
                } else {
                    $this->collectImagesFromJson($value, $urls);
                }
            }
        }
    }

    /**
     * Extract image URLs from HTML content.
     * Looks for images in product gallery containers, carousels, and img tags.
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
        
        // XPath queries for product images (common Logitech patterns)
        $queries = [
            // Product gallery containers
            "//*[contains(@class, 'product-gallery')]//img",
            "//*[contains(@class, 'product-images')]//img",
            "//*[contains(@class, 'gallery')]//img",
            "//*[contains(@class, 'carousel')]//img",
            "//*[contains(@class, 'slider')]//img",
            "//*[contains(@class, 'swiper')]//img",
            "//*[contains(@data-testid, 'product-image')]//img",
            "//*[contains(@data-testid, 'gallery')]//img",
            
            // Picture elements with source tags
            "//*[contains(@class, 'product-gallery')]//source",
            "//*[contains(@class, 'product-images')]//source",
            "//*[contains(@class, 'gallery')]//source",
            
            // Fallback: all images
            "//img",
        ];
        
        $urls = [];

        // Extract from img tags
        foreach ($queries as $query) {
            $nodes = $xpath->query($query);
            
            if (! $nodes) {
                continue;
            }

            foreach ($nodes as $node) {
                if (! ($node instanceof \DOMElement)) {
                    continue;
                }

                $src = '';

                // 1) src attribute
                $src = trim($node->getAttribute('src'));

                // 2) data-src for lazy loading
                if ($src === '' || $src === 'data:image' || str_starts_with($src, 'data:')) {
                    $src = trim($node->getAttribute('data-src') ?? '');
                }

                // 3) data-lazy-src
                if ($src === '' || $src === 'data:image' || str_starts_with($src, 'data:')) {
                    $src = trim($node->getAttribute('data-lazy-src') ?? '');
                }

                // 4) data-original
                if ($src === '' || $src === 'data:image' || str_starts_with($src, 'data:')) {
                    $src = trim($node->getAttribute('data-original') ?? '');
                }

                // 5) data-srcset (first URL in srcset)
                if ($src === '' || $src === 'data:image' || str_starts_with($src, 'data:')) {
                    $srcset = trim($node->getAttribute('data-srcset') ?? '');
                    if ($srcset !== '') {
                        $parts = preg_split('/\s*,\s*/', $srcset);
                        if (! empty($parts)) {
                            $first = trim(explode(' ', $parts[0])[0]);
                            $src = $first;
                        }
                    }
                }

                // 6) srcset attribute
                if ($src === '' || $src === 'data:image' || str_starts_with($src, 'data:')) {
                    $srcset = trim($node->getAttribute('srcset') ?? '');
                    if ($srcset !== '') {
                        $parts = preg_split('/\s*,\s*/', $srcset);
                        // Prefer webp or larger images
                        foreach ($parts as $part) {
                            $urlPart = trim(explode(' ', trim($part))[0]);
                            if (str_contains(strtolower($urlPart), '.webp') || empty($src) || $src === 'data:image') {
                                $src = $urlPart;
                                if (str_contains(strtolower($urlPart), '.webp')) {
                                    break; // Prefer webp
                                }
                            }
                        }
                    }
                }

                // 7) style="background-image:url(...)"
                if ($src === '' || $src === 'data:image' || str_starts_with($src, 'data:')) {
                    $style = (string) $node->getAttribute('style');
                    $bg = $this->extractBackgroundImageUrl($style);
                    if ($bg !== null) {
                        $src = $bg;
                    }
                }

                // Handle source tags (for picture elements)
                if ($node->tagName === 'source') {
                    $srcset = trim($node->getAttribute('srcset') ?? '');
                    if ($srcset !== '') {
                        $parts = preg_split('/\s*,\s*/', $srcset);
                        foreach ($parts as $part) {
                            $urlPart = trim(explode(' ', trim($part))[0]);
                            if ($urlPart !== '') {
                                $absolute = $this->toAbsoluteUrl($urlPart, $baseUrl);
                                if ($this->looksLikeImageUrl($absolute) && ! in_array($absolute, $urls, true)) {
                                    $urls[] = $absolute;
                                }
                            }
                        }
                    }
                    continue;
                }

                if ($src === '' || str_starts_with($src, 'data:')) {
                    continue;
                }

                // Only include image URLs
                if (! $this->looksLikeImageUrl($src)) {
                    continue;
                }

                $absolute = $this->toAbsoluteUrl($src, $baseUrl);

                if (! in_array($absolute, $urls, true)) {
                    $urls[] = $absolute;
                }
            }
        }

        // Also try regex extraction for logitech.com CDN URLs
        $regexUrls = $this->extractLogitechUrlsWithRegex($html);
        $urls = array_merge($urls, $regexUrls);

        // Make unique and reindex
        return array_values(array_unique($urls));
    }

    /**
     * Extract Logitech image URLs using regex (for cases where DOM parsing might miss them).
     *
     * @param  string  $html
     * @return array<int, string>
     */
    protected function extractLogitechUrlsWithRegex(string $html): array
    {
        $urls = [];

        // Pattern for logitech.com image URLs
        $patterns = [
            '#https?://[^"\'\s<>]+logitech[^"\'\s<>]+\.(?:jpe?g|png|webp|gif)(?:\?[^\s<>"\']*)?#i',
            '#["\'](https?://[^"\']*logitech[^"\']*\.(?:jpe?g|png|webp|gif)[^"\']*)["\']#i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $html, $matches)) {
                foreach ($matches[1] ?? $matches[0] as $url) {
                    $trimmed = trim($url);
                    $trimmed = rtrim($trimmed, '",\'})];');
                    
                    if (str_contains($trimmed, '?')) {
                        $parts = explode('?', $trimmed, 2);
                        $trimmed = $parts[0];
                    }
                    
                    if ($trimmed !== '' && $this->looksLikeImageUrl($trimmed) && ! in_array($trimmed, $urls, true)) {
                        $urls[] = $trimmed;
                    }
                }
            }
        }

        return $urls;
    }

    /**
     * Clean Logitech CDN URLs to extract the actual image paths.
     * Logitech uses transformation URLs like: .../d_transparent.gif/content/dam/.../image.png
     * This extracts the actual image path or returns optimized URLs.
     *
     * @param  array<int, string>  $urls
     * @return array<int, string>
     */
    protected function cleanLogitechUrls(array $urls): array
    {
        $cleaned = [];

        foreach ($urls as $url) {
            // If URL contains /d_transparent.gif/ or similar transformation markers,
            // extract the actual image path after it
            if (preg_match('#/d_[^/]+/content/dam/(.+)$#i', $url, $matches)) {
                // Reconstruct a cleaner URL with just the transformation parameters needed
                // Keep the base CDN domain but simplify the transformation
                $baseUrl = preg_replace('#/d_[^/]+/content/dam/.+$#i', '', $url);
                $imagePath = '/content/dam/' . $matches[1];
                
                // Create optimized URL without transparent placeholder
                // Logitech CDN: resource.logitech.com/c_fill,q_auto,f_auto,dpr_1.0/content/dam/...
                $cleanUrl = $baseUrl . '/content/dam/' . $matches[1];
                
                // Remove query parameters and fragments for cleaner output
                if (strpos($cleanUrl, '?') !== false) {
                    $cleanUrl = strtok($cleanUrl, '?');
                }
                
                if (! in_array($cleanUrl, $cleaned, true)) {
                    $cleaned[] = $cleanUrl;
                }
            } else {
                // URL doesn't have transformation marker, use as-is
                $cleanUrl = $url;
                if (strpos($cleanUrl, '?') !== false) {
                    $cleanUrl = strtok($cleanUrl, '?');
                }
                if (! in_array($cleanUrl, $cleaned, true)) {
                    $cleaned[] = $cleanUrl;
                }
            }
        }

        return array_values($cleaned);
    }

    /**
     * Filter image URLs to only include product images (exclude logos, icons, banners).
     *
     * @param  array<int, string>  $urls
     * @return array<int, string>
     */
    protected function filterProductImages(array $urls): array
    {
        $productUrls = [];

        $excludePatterns = [
            '#logo#i',
            '#icon#i',
            '#banner#i',
            '#header#i',
            '#footer#i',
            '#nav#i',
            '#social#i',
            '#favicon#i',
            '#\.svg#i', // SVG files are usually icons/logos
        ];

        foreach ($urls as $url) {
            // Exclude if matches exclude patterns
            $shouldExclude = false;
            foreach ($excludePatterns as $pattern) {
                if (preg_match($pattern, $url)) {
                    $shouldExclude = true;
                    break;
                }
            }

            if ($shouldExclude) {
                continue;
            }

            // Include URLs that look like product images
            // Common patterns: product, images, gallery, assets with product codes
            $includePatterns = [
                '#product#i',
                '#images?/product#i',
                '#gallery#i',
                '#assets.*product#i',
                '#/[0-9a-f]{8,}#i', // Product ID patterns
            ];

            $shouldInclude = false;
            foreach ($includePatterns as $pattern) {
                if (preg_match($pattern, $url)) {
                    $shouldInclude = true;
                    break;
                }
            }

            // If no include pattern matches but it's from logitech.com and not excluded, include it
            if (! $shouldInclude && str_contains(strtolower($url), 'logitech.com')) {
                // Check file size hints in URL (common for product images)
                if (preg_match('#/(\d{3,4}x\d{3,4}|large|medium|high)/#i', $url)) {
                    $shouldInclude = true;
                } elseif (preg_match('#\.(jpe?g|png|webp)$#i', $url)) {
                    // Include if it's a standard image format and not too small (likely icon)
                    if (! preg_match('#/\d{1,2}x\d{1,2}/#i', $url)) {
                        $shouldInclude = true;
                    }
                }
            }

            if ($shouldInclude) {
                $productUrls[] = $url;
            }
        }

        return $productUrls;
    }

    /**
     * Extract URL from CSS background-image definition in style attribute.
     */
    protected function extractBackgroundImageUrl(string $style): ?string
    {
        if ($style === '') {
            return null;
        }

        if (preg_match('#background-image\s*:\s*url\((["\']?)([^)]+?)\1\)#i', $style, $m)) {
            $url = trim($m[2], " \t\n\r\0\x0B'\"");
            return $url !== '' ? $url : null;
        }

        return null;
    }

    /**
     * Check if a string looks like an image URL (jpg, jpeg, png, webp, gif).
     */
    protected function looksLikeImageUrl(string $value): bool
    {
        return (bool) preg_match('#\.(jpe?g|png|webp|gif)(\?|$)#i', $value);
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