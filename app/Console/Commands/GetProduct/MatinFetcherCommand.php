<?php

namespace App\Console\Commands\GetProduct;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class MatinFetcherCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:matin-fetcher {url : Product page URL on matin.co}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and display image URLs from a Matin product page';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url');

        $this->info("Fetching HTML from: {$url}");

        try {
            $response = Http::get($url);
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

        if (empty($imageUrls)) {
            $this->warn('No images were found on this page.');
            return self::SUCCESS;
        }

        $this->info('Found image URLs:');
        foreach ($imageUrls as $imgUrl) {
            $this->line($imgUrl);
        }

        return self::SUCCESS;
    }

    /**
     * Extract image URLs from HTML content.
     * Prioritizes images from woocommerce-product-gallery container.
     * Prefers .webp images when available.
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
        
        // First priority: woocommerce-product-gallery (product images)
        $productGalleryQuery = "//*[contains(@class, 'woocommerce-product-gallery')]//img[@src or @data-src]";
        
        $urls = [];
        $webpUrls = [];
        $otherUrls = [];

        // Extract from product gallery first
        $nodes = $xpath->query($productGalleryQuery);
        if ($nodes) {
            foreach ($nodes as $node) {
                /** @var \DOMElement $node */
                $src = trim($node->getAttribute('src') ?? '');
                
                // Also check data-src for lazy-loaded images
                if (empty($src) || $src === 'data:image') {
                    $src = trim($node->getAttribute('data-src') ?? '');
                }

                // Check data-large_image for high-res images (often .webp)
                if (empty($src) || $src === 'data:image') {
                    $src = trim($node->getAttribute('data-large_image') ?? '');
                }

                // Check data-srcset for responsive images
                if (empty($src) || $src === 'data:image') {
                    $srcset = trim($node->getAttribute('data-srcset') ?? '');
                    if ($srcset) {
                        // Extract first URL from srcset, prefer .webp if available
                        $srcsetUrls = preg_split('/\s*,\s*/', $srcset);
                        foreach ($srcsetUrls as $srcsetItem) {
                            $parts = preg_split('/\s+/', trim($srcsetItem), 2);
                            if (!empty($parts[0])) {
                                $candidate = $parts[0];
                                if (str_ends_with(strtolower($candidate), '.webp')) {
                                    $src = $candidate;
                                    break;
                                } elseif (empty($src) || $src === 'data:image') {
                                    $src = $candidate;
                                }
                            }
                        }
                    }
                }

                if ($src === '' || $src === 'data:image') {
                    continue;
                }

                $absolute = $this->toAbsoluteUrl($src, $baseUrl);
                
                // Prioritize .webp images
                if (str_ends_with(strtolower($absolute), '.webp')) {
                    if (! in_array($absolute, $webpUrls, true)) {
                        $webpUrls[] = $absolute;
                    }
                } else {
                    // Try to convert to .webp (common pattern: replace extension)
                    $webpUrl = $this->getWebpUrl($absolute);
                    if ($webpUrl && $webpUrl !== $absolute) {
                        // Add both original and webp, but prioritize webp
                        if (! in_array($webpUrl, $webpUrls, true)) {
                            $webpUrls[] = $webpUrl;
                        }
                    } else {
                        if (! in_array($absolute, $otherUrls, true)) {
                            $otherUrls[] = $absolute;
                        }
                    }
                }
            }
        }

        // Combine: webp first, then others
        $urls = array_merge($webpUrls, $otherUrls);

        return $urls;
    }

    /**
     * Try to convert an image URL to .webp format.
     * 
     * @param  string  $url
     * @return string|null
     */
    protected function getWebpUrl(string $url): ?string
    {
        // If already .webp, return as is
        if (str_ends_with(strtolower($url), '.webp')) {
            return $url;
        }

        // Try common patterns to convert to .webp
        $patterns = [
            '/\.(jpg|jpeg|png|gif)(\?.*)?$/i' => '.webp$2',
            '/\.(jpg|jpeg|png|gif)$/i' => '.webp',
        ];

        foreach ($patterns as $pattern => $replacement) {
            if (preg_match($pattern, $url)) {
                $webpUrl = preg_replace($pattern, $replacement, $url);
                return $webpUrl;
            }
        }

        // Try appending .webp before query string
        if (strpos($url, '?') !== false) {
            $parts = explode('?', $url, 2);
            $base = $parts[0];
            $query = $parts[1];
            
            // Remove existing extension and add .webp
            $base = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '', $base);
            return $base . '.webp?' . $query;
        }

        return null;
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
