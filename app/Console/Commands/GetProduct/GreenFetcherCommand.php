<?php

namespace App\Console\Commands\GetProduct;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GreenFetcherCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:green-fetcher {url : Product page URL on green.ir}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and display image URLs from a Green product page carousel';

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
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])->timeout(30)->get($url);
        } catch (\Throwable $e) {
            $this->error('Request failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        if (! $response->ok()) {
            $this->error('Request failed with status code: ' . $response->status());
            return self::FAILURE;
        }

        $html = $response->body();

        $allImageUrls = $this->extractImageUrls($html, $url);

        // فقط آدرس‌های گالری با سایز 375x375 را فیلتر کن
        $imageUrls = $this->filterGalleryImages($allImageUrls);

        if (empty($imageUrls)) {
            $this->warn('No gallery images (375x375) were found on this page.');
            if (! empty($allImageUrls)) {
                $this->info('Found ' . count($allImageUrls) . ' total images, but none matched gallery pattern.');
            }
            return self::SUCCESS;
        }

        $this->info('Found gallery image URLs (375x375):');
        foreach ($imageUrls as $imgUrl) {
            $this->line($imgUrl);
        }

        return self::SUCCESS;
    }

    /**
     * Extract image URLs from HTML content of a Green product page.
     * Focus on:
     * - single-product-carousel
     * - owl-carousel / owl-theme
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

        // XPath queries targeting Green's carousel structure
        $queries = [
            // اصلی: اسلایدر محصول
            "//*[contains(@class, 'single-product-carousel')]//img",
            "//*[contains(@class, 'single-product-carousel')]//*[contains(@class, 'owl-item')]//img",

            // هر اسلایدر owl-carousel روی صفحه
            "//*[contains(@class, 'owl-carousel')]//img",

            // fallback کلی
            "//img",
        ];

        $urls = [];

        foreach ($queries as $query) {
            $nodes = $xpath->query($query);

            if (! $nodes) {
                continue;
            }

            foreach ($nodes as $node) {
                $src = '';

                if ($node instanceof \DOMElement) {
                    // 1) src
                    $src = trim($node->getAttribute('src'));

                    // 2) data-src
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

                    // 5) data-srcset (اولین url)
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

                    // 6) style="background-image:url(...)"
                    if ($src === '' || $src === 'data:image' || str_starts_with($src, 'data:')) {
                        $style = (string) $node->getAttribute('style');
                        $bg = $this->extractBackgroundImageUrl($style);
                        if ($bg !== null) {
                            $src = $bg;
                        }
                    }
                }

                if ($src === '' || str_starts_with($src, 'data:')) {
                    continue;
                }

                if (! $this->looksLikeImageUrl($src)) {
                    continue;
                }

                $absolute = $this->toAbsoluteUrl($src, $baseUrl);

                if (! in_array($absolute, $urls, true)) {
                    $urls[] = $absolute;
                }
            }
        }

        return array_values(array_unique($urls));
    }

    /**
     * Filter image URLs to only include gallery images with 375x375 size.
     * Pattern: URLs containing "Gallery/" and ending with "_375_375.jpg"
     *
     * @param  array<int, string>  $urls
     * @return array<int, string>
     */
    protected function filterGalleryImages(array $urls): array
    {
        $filtered = [];

        foreach ($urls as $url) {
            // فقط آدرس‌هایی که شامل Gallery/ هستند و با _375_375.jpg تمام می‌شوند
            if (
                str_contains($url, 'Gallery/') &&
                preg_match('#_375_375\.jpg(\?|$)#i', $url)
            ) {
                $filtered[] = $url;
            }
        }

        return array_values(array_unique($filtered));
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
     * Check if a string looks like an image URL.
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
        if (substr($path, -1) !== '/') {
            $path = dirname($path) . '/';
        }

        return "{$scheme}://{$host}{$port}" . $path . $src;
    }
}
