<?php

namespace App\Console\Commands\GetProduct;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class XVisionFetcherCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:x-vision-fetcher {url : Product page URL on xvision.ir}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and display image URLs from an XVision product page';

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
                'Accept-Language' => 'fa-IR,fa;q=0.9,en-US;q=0.8,en;q=0.7',
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
            return self::FAILURE;
        }

        $html = $response->body();

        $imageUrls = $this->extractImageUrls($html, $url);

        // Filter to only product images (from uploads directory, exclude logos/icons)
        $productImages = $this->filterProductImages($imageUrls);

        if (empty($productImages)) {
            $this->warn('No product images were found on this page.');
            if (! empty($imageUrls)) {
                $this->warn('Found ' . count($imageUrls) . ' total images, but none matched product image pattern.');
            }
            return self::SUCCESS;
        }

        $this->info('Found product image URLs:');
        foreach ($productImages as $imgUrl) {
            $this->line($imgUrl);
        }

        return self::SUCCESS;
    }

    /**
     * Extract image URLs from HTML content.
     * Focuses on images in owl-stage-outer container (Owl Carousel).
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
        
        // XPath queries for Owl Carousel containers
        // Priority: owl-stage-outer (as mentioned by user)
        // Only extract from owl carousel containers to avoid logos/icons
        $queries = [
            // owl-stage-outer container (main priority)
            "//*[contains(@class, 'owl-stage-outer')]//img",
            "//*[contains(@class, 'owl-stage')]//img",
            "//*[contains(@class, 'owl-item')]//img",
            "//*[contains(@class, 'owl-carousel')]//img",
        ];

        $urls = [];

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
                $src = trim($node->getAttribute('src') ?? '');

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

                // 5) data-srcset (extract first URL)
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
                        if (! empty($parts)) {
                            $first = trim(explode(' ', $parts[0])[0]);
                            $src = $first;
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

        // Also extract from source tags in picture elements (only from owl carousel)
        $sourceQueries = [
            "//*[contains(@class, 'owl-stage-outer')]//source",
            "//*[contains(@class, 'owl-stage')]//source",
            "//*[contains(@class, 'owl-item')]//source",
            "//*[contains(@class, 'owl-carousel')]//source",
        ];

        foreach ($sourceQueries as $query) {
            $nodes = $xpath->query($query);
            
            if (! $nodes) {
                continue;
            }

            foreach ($nodes as $node) {
                if (! ($node instanceof \DOMElement)) {
                    continue;
                }

                // Extract srcset from source tag
                $srcset = trim($node->getAttribute('srcset') ?? '');
                if ($srcset === '') {
                    $srcset = trim($node->getAttribute('data-srcset') ?? '');
                }
                
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

                // Extract src from source tag
                $src = trim($node->getAttribute('src') ?? '');
                if ($src === '' || str_starts_with($src, 'data:')) {
                    $src = trim($node->getAttribute('data-src') ?? '');
                }
                
                if ($src !== '' && ! str_starts_with($src, 'data:')) {
                    $absolute = $this->toAbsoluteUrl($src, $baseUrl);
                    if ($this->looksLikeImageUrl($absolute) && ! in_array($absolute, $urls, true)) {
                        $urls[] = $absolute;
                    }
                }
            }
        }

        // Make unique and reindex
        $urls = array_values(array_unique($urls));

        return $urls;
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
     * Filter image URLs to only include product images.
     * Excludes logos, icons, and other non-product images.
     *
     * @param  array<int, string>  $urls
     * @return array<int, string>
     */
    protected function filterProductImages(array $urls): array
    {
        $productImages = [];

        // Patterns to exclude (non-product images)
        $excludePatterns = [
            '/themes/',           // Theme images
            '/assets/',           // Asset images
            '/icon/',             // Icons
            'logo',               // Logos
            'enamad',             // Trust badges
            'instagram',          // Social media icons
            'linkedin',
            'footer',
            'arrow',
            'show_icon',
            'hide_icon',
            'shop.png',
            'Guarantee',
            'Warranty',
            'moshver',
            'Ersal',
            'Safe-pay',
            'صوتی-تصویری',        // Category images
            'آشپزخونه',
            'خانگی',
            'کولرگازی',
            'REF-WM-MW',
            'AC-F',
            'Untitled',
            'Bluetooth',
            'android',
            'ips',
            'memc',
            'hdr',
            '4k',
            'gled',
            'wqhd',
            '180',
            '1-ms',
            'DP',
            'pivot',
            'anti-glare',
            'flicker',
            'A-4',
        ];

        foreach ($urls as $url) {
            $lowerUrl = strtolower($url);
            
            // Skip if matches any exclude pattern
            $shouldExclude = false;
            foreach ($excludePatterns as $pattern) {
                if (str_contains($lowerUrl, strtolower($pattern))) {
                    $shouldExclude = true;
                    break;
                }
            }

            if ($shouldExclude) {
                continue;
            }

            // Only include images from uploads directory (product images)
            // Pattern: /wp-content/uploads/YYYY/MM/filename.ext
            if (preg_match('#/wp-content/uploads/\d{4}/\d{2}/[^/]+\.(jpe?g|png|webp|gif)#i', $url)) {
                $productImages[] = $url;
            }
        }

        return array_values(array_unique($productImages));
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
