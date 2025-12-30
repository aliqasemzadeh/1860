<?php

namespace App\Console\Commands\GetProduct;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GigaByteFetcherCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:giga-byte-fetcher {url : Product gallery page or API URL on gigabyte.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and display gallery image URLs from a Gigabyte product gallery page or API';

    /**
     * Store HTML content for later use in URL completion.
     *
     * @var string|null
     */
    protected $htmlContent = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url');

        // If it's a gallery page URL, try to build API URL or use as is
        $apiUrl = $this->buildApiUrl($url);
        
        if ($apiUrl !== $url) {
            $this->info("Gallery page detected: {$url}");
            $this->info("Fetching from API: {$apiUrl}");
        } else {
            $this->info("Fetching from: {$url}");
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Referer' => 'https://www.gigabyte.com/',
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

        // If we get 403, try with simpler headers as fallback
        if ($response->status() === 403) {
            $this->warn('Received 403, trying with simpler headers...');
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                ])
                ->timeout(30)
                ->get($url);
            } catch (\Throwable $e) {
                $this->error('Fallback request also failed: ' . $e->getMessage());
                return self::FAILURE;
            }
        }

        if (! $response->ok()) {
            $this->error('Request failed with status code: ' . $response->status());
            $this->warn('The website may be blocking automated requests. You might need to:');
            $this->warn('1. Check if the URL is accessible in a browser');
            $this->warn('2. The site may require JavaScript or cookies');
            $this->warn('3. Try accessing the page API directly if available');
            return self::FAILURE;
        }

        $imageUrls = [];
        $this->htmlContent = null; // Store HTML for later use
        $contentType = $response->header('Content-Type', '');

        // If API returns JSON, extract image URLs from JSON payload
        if (str_contains($contentType, 'application/json')) {
            $json = $response->json();

            if (! is_array($json)) {
                $this->error('Invalid JSON response structure.');
                return self::FAILURE;
            }

            $this->info('Detected JSON response from API. Extracting image URLs from JSON...');
            $imageUrls = $this->extractImageUrlsFromApi($json);
        } else {
            // Treat as HTML gallery page and extract images
            $html = $response->body();
            $this->htmlContent = $html; // Store for later use
            $this->info('Detected HTML response. Extracting image URLs from gallery...');

            // Debug: Check if static.gigabyte.com URLs exist in HTML
            $staticCount = substr_count(strtolower($html), 'static.gigabyte.com');
            $productCount = substr_count(strtolower($html), '/product/');
            if ($staticCount > 0) {
                $this->info("Found {$staticCount} occurrences of 'static.gigabyte.com' in HTML.");
            } else {
                $this->warn("No 'static.gigabyte.com' URLs found in initial HTML. Page may load images dynamically with JavaScript.");
            }
            if ($productCount > 0) {
                $this->info("Found {$productCount} occurrences of '/Product/' in HTML.");
            } else {
                $this->warn("No '/Product/' URLs found in initial HTML. Images may be loaded via JavaScript.");
            }

            // Try to find API endpoint from HTML
            $apiEndpoint = $this->findApiEndpoint($html, $url);
            if ($apiEndpoint !== null) {
                $this->info('Found API endpoint, fetching from API...');
                $this->line($apiEndpoint);
                
                try {
                    $apiResponse = Http::withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'Accept' => 'application/json, text/plain, */*',
                        'Referer' => $url,
                    ])
                    ->timeout(30)
                    ->get($apiEndpoint);
                    
                    if ($apiResponse->ok() && str_contains($apiResponse->header('Content-Type', ''), 'application/json')) {
                        $json = $apiResponse->json();
                        if (is_array($json)) {
                            $imageUrls = $this->extractImageUrlsFromApi($json);
                            $this->info('Successfully fetched images from API.');
                        }
                    }
                } catch (\Throwable $e) {
                    $this->warn('API request failed: ' . $e->getMessage());
                    $this->warn('Falling back to HTML extraction...');
                }
            }

            // If API didn't work, try HTML extraction with multiple attempts (for JavaScript-loaded content)
            if (empty($imageUrls)) {
                // Try to find "Download Images" link and show it
                $downloadUrl = $this->extractDownloadImagesUrl($html, $url);
                if ($downloadUrl !== null) {
                    $this->info('Found "Download Images" link:');
                    $this->line($downloadUrl);
                }

                $imageUrls = $this->extractImageUrls($html, $url);
                
                // If no Product URLs found, wait and retry (for JavaScript-loaded content)
                $productUrls = array_filter($imageUrls, function ($url) {
                    return str_contains(strtolower($url), '/product/');
                });
                
                if (empty($productUrls) && str_contains(strtolower($html), 'static.gigabyte.com')) {
                    $this->info('No Product URLs found in initial HTML. Waiting for JavaScript to load...');
                    sleep(3); // Wait 3 seconds for JavaScript to load
                    
                    try {
                        $retryResponse = Http::withHeaders([
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                            'Referer' => 'https://www.gigabyte.com/',
                        ])
                        ->timeout(30)
                        ->get($url);
                        
                        if ($retryResponse->ok()) {
                            $retryHtml = $retryResponse->body();
                            $retryUrls = $this->extractImageUrls($retryHtml, $url);
                            $imageUrls = array_merge($imageUrls, $retryUrls);
                            $imageUrls = array_values(array_unique($imageUrls));
                        }
                    } catch (\Throwable $e) {
                        $this->warn('Retry failed: ' . $e->getMessage());
                    }
                }
            }
        }

        if (empty($imageUrls)) {
            $this->warn('No images were found.');
            return self::SUCCESS;
        }

        // Step 1: Show all found images
        $this->info('=== Step 1: All found image URLs ===');
        foreach ($imageUrls as $imgUrl) {
            $this->line($imgUrl);
        }
        $this->newLine();

        // Step 2: Filter only static.gigabyte.com URLs that contain /Product (with or without trailing slash)
        // We only want Product URLs, not TopMenuNormal or other categories
        $staticUrls = array_filter($imageUrls, function ($url) {
            $lowerUrl = strtolower($url);
            return str_contains($lowerUrl, 'static.gigabyte.com') && 
                   (str_contains($lowerUrl, '/product/') || str_contains($lowerUrl, '/product'));
        });
        
        if (empty($staticUrls)) {
            $this->warn('No Product URLs found in static.gigabyte.com URLs.');
            $this->info('All found URLs (for debugging):');
            foreach ($imageUrls as $imgUrl) {
                $this->line($imgUrl);
            }
            return self::SUCCESS;
        }

        if (empty($staticUrls)) {
            $this->warn('No static.gigabyte.com URLs were found.');
            return self::SUCCESS;
        }

        $this->info('=== Step 2: Static.gigabyte.com URLs ===');
        foreach ($staticUrls as $imgUrl) {
            $this->line($imgUrl);
        }
        $this->newLine();

        // Step 3: Convert PNG URLs to webp and filter webp URLs
        // Also complete incomplete URLs (those ending with /Product)
        // First, try to extract Product ID from existing URLs or HTML
        $productIds = [];
        foreach ($staticUrls as $url) {
            if (preg_match('#/Product/(\d+)#i', $url, $match)) {
                $productIds[] = $match[1];
            }
        }
        $productIds = array_unique($productIds);
        
        // If no Product ID found in URLs, try to extract from HTML (if available)
        if (empty($productIds) && $this->htmlContent !== null) {
            if (preg_match_all('#/Product/(\d+)#i', $this->htmlContent, $htmlMatches)) {
                $productIds = array_unique($htmlMatches[1]);
            }
        }
        
        // Use only the largest size (1200)
        $largestSize = 1200;
        
        $webpUrls = [];
        foreach ($staticUrls as $url) {
            $lowerUrl = strtolower($url);
            
            // If URL is incomplete (ends with /Product), try to complete it with largest size only
            if (preg_match('#/product$#i', $url) || preg_match('#/product/$#i', $url)) {
                $baseUrl = rtrim($url, '/');
                
                // Try each Product ID with largest size only
                if (! empty($productIds)) {
                    foreach ($productIds as $productId) {
                        $completeUrl = "{$baseUrl}/{$productId}/webp/{$largestSize}";
                        if (! in_array($completeUrl, $webpUrls, true)) {
                            $webpUrls[] = $completeUrl;
                        }
                    }
                } else {
                    // If Product ID not found, try common Product IDs
                    $commonProductIds = ['47498', '47499', '47500'];
                    foreach ($commonProductIds as $productId) {
                        $completeUrl = "{$baseUrl}/{$productId}/webp/{$largestSize}";
                        if (! in_array($completeUrl, $webpUrls, true)) {
                            $webpUrls[] = $completeUrl;
                        }
                    }
                }
            }
            // If it's already webp, convert to largest size if it has a size parameter
            elseif (str_contains($lowerUrl, '.webp') || str_contains($lowerUrl, '/webp/')) {
                // If URL has a size (e.g., /webp/670), replace with largest size
                if (preg_match('#/webp/(\d+)#i', $url, $sizeMatch)) {
                    $largestSizeUrl = preg_replace('#/webp/\d+#i', "/webp/{$largestSize}", $url);
                    if (! in_array($largestSizeUrl, $webpUrls, true)) {
                        $webpUrls[] = $largestSizeUrl;
                    }
                } else {
                    // If no size found, add as is
                    if (! in_array($url, $webpUrls, true)) {
                        $webpUrls[] = $url;
                    }
                }
            }
            // If it's PNG, convert to webp with largest size
            elseif (str_contains($lowerUrl, '/png/')) {
                $webpUrl = str_replace('/png/', '/webp/', $url);
                $webpUrl = str_replace('.png', '.webp', $webpUrl);
                // Replace size with largest if found
                if (preg_match('#/webp/(\d+)#i', $webpUrl, $sizeMatch)) {
                    $webpUrl = preg_replace('#/webp/\d+#i', "/webp/{$largestSize}", $webpUrl);
                }
                if (! in_array($webpUrl, $webpUrls, true)) {
                    $webpUrls[] = $webpUrl;
                }
            }
            // If it's jpg/jpeg, try to convert to webp with largest size
            elseif (str_contains($lowerUrl, '/jpg/') || str_contains($lowerUrl, '/jpeg/')) {
                $webpUrl = str_replace(['/jpg/', '/jpeg/'], '/webp/', $url);
                $webpUrl = preg_replace('/\.(jpg|jpeg)$/i', '.webp', $webpUrl);
                // Replace size with largest if found
                if (preg_match('#/webp/(\d+)#i', $webpUrl, $sizeMatch)) {
                    $webpUrl = preg_replace('#/webp/\d+#i', "/webp/{$largestSize}", $webpUrl);
                }
                if (! in_array($webpUrl, $webpUrls, true)) {
                    $webpUrls[] = $webpUrl;
                }
            }
        }

        if (empty($webpUrls)) {
            $this->warn('No webp images were found or could be converted.');
            return self::SUCCESS;
        }

        $this->info('=== Step 3: Webp URLs (final result) ===');
        foreach ($webpUrls as $imgUrl) {
            $this->line($imgUrl);
        }

        return self::SUCCESS;
    }

    /**
     * Build API URL from gallery page URL or return original URL if it's already an API URL.
     * 
     * @param  string  $url
     * @return string
     */
    protected function buildApiUrl(string $url): string
    {
        // If it's already an API URL, return as is
        if (str_contains($url, 'api.gigabyte.com') || str_contains($url, '/api/')) {
            return $url;
        }

        // Extract product path from gallery URL
        // Pattern: https://www.gigabyte.com/Motherboard/{product-path}/gallery
        if (preg_match('#gigabyte\.com/([^/]+/[^/]+/[^/]+)/gallery#i', $url, $matches)) {
            $productPath = $matches[1];
            // Try common API patterns (may need adjustment based on actual API structure)
            // For now, return original URL and let HTML parsing handle it
        }

        return $url;
    }

    /**
     * Try to find API endpoint from HTML (JavaScript, JSON-LD, or data attributes).
     *
     * @param  string  $html
     * @param  string  $baseUrl
     * @return string|null
     */
    protected function findApiEndpoint(string $html, string $baseUrl): ?string
    {
        // Pattern 1: Look for API URLs in JavaScript (fetch, axios, ajax calls)
        $patterns = [
            // fetch('api/...') or fetch("api/...")
            '#fetch\(["\']([^"\']*api[^"\']*product[^"\']*gallery[^"\']*)["\']#i',
            // axios.get('api/...')
            '#axios\.(?:get|post)\(["\']([^"\']*api[^"\']*product[^"\']*)["\']#i',
            // $.ajax({url: 'api/...'})
            '#ajax\([^}]*url\s*:\s*["\']([^"\']*api[^"\']*product[^"\']*)["\']#i',
            // Direct API URLs in script tags
            '#(https?://[^"\'\s<>]+api[^"\'\s<>]+product[^"\'\s<>]+)#i',
            // API URLs with /api/ path
            '#(https?://[^"\'\s<>]+/api/[^"\'\s<>]+)#i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $apiUrl = trim($matches[1]);
                if (! empty($apiUrl)) {
                    // Make absolute if relative
                    if (! preg_match('#^https?://#i', $apiUrl)) {
                        $apiUrl = $this->toAbsoluteUrl($apiUrl, $baseUrl);
                    }
                    return $apiUrl;
                }
            }
        }

        // Pattern 2: Look for data attributes with API URLs
        if (preg_match('#data-api-url=["\']([^"\']+)["\']#i', $html, $matches)) {
            $apiUrl = trim($matches[1]);
            if (! empty($apiUrl)) {
                if (! preg_match('#^https?://#i', $apiUrl)) {
                    $apiUrl = $this->toAbsoluteUrl($apiUrl, $baseUrl);
                }
                return $apiUrl;
            }
        }

        // Pattern 3: Try to construct API URL from product path
        if (preg_match('#gigabyte\.com/([^/]+/[^/]+/[^/]+)/gallery#i', $baseUrl, $matches)) {
            $productPath = $matches[1];
            // Try common API patterns
            $possibleApis = [
                "https://www.gigabyte.com/api/product/{$productPath}/gallery",
                "https://api.gigabyte.com/product/{$productPath}/gallery",
                "https://www.gigabyte.com/api/{$productPath}/images",
            ];
            
            // We can't test all of them here, so return null and let the caller handle it
            // Or we could try the first one
        }

        return null;
    }

    /**
     * Extract image URLs from HTML gallery content.
     * Looks for images in gallery containers, img tags, and data attributes.
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
        
        // XPath queries for gallery images
        // مخصوص گیگابایت: گالری در swiper با کلاس‌هایی مثل js-galleryModalSwiper و gallery-modal-swiper است
        $queries = [
            // swiper-wrapper و modal-thumbnail-show-image
            "//*[contains(@class, 'swiper-wrapper')]//*[contains(@class, 'modal-thumbnail-show-image')]//img",
            "//*[contains(@class, 'swiper-wrapper')]//*[contains(@class, 'lazyFadeIn')]//img",
            "//*[contains(@class, 'swiper-wrapper')]//*[contains(@class, 'entered')]//img",
            "//*[contains(@class, 'swiper-wrapper')]//img",
            
            // Gigabyte gallery modal swiper
            "//*[contains(@class, 'js-galleryModalSwiper') or contains(@class, 'gallery-modal-swiper')]//img",
            "//*[contains(@class, 'js-galleryModalSwiper') or contains(@class, 'gallery-modal-swiper')]//*[contains(@class, 'swiper-slide')]//img",

            // سایر گالری‌ها
            "//*[contains(@class, 'gallery')]//img",
            "//*[contains(@class, 'product-gallery')]//img",
            "//*[contains(@class, 'swiper')]//img",
            "//*[contains(@id, 'gallery')]//img",

            // fallback کلی
            "//img",
        ];
        
        // Queries for source tags (used in picture elements)
        $sourceQueries = [
            "//*[contains(@class, 'swiper-wrapper')]//*[contains(@class, 'modal-thumbnail-show-image')]//source",
            "//*[contains(@class, 'swiper-wrapper')]//*[contains(@class, 'lazyFadeIn')]//source",
            "//*[contains(@class, 'swiper-wrapper')]//*[contains(@class, 'entered')]//source",
            "//*[contains(@class, 'swiper-wrapper')]//source",
            "//*[contains(@class, 'js-galleryModalSwiper') or contains(@class, 'gallery-modal-swiper')]//source",
            "//*[contains(@class, 'gallery')]//source",
            "//*[contains(@class, 'swiper')]//source",
            "//source",
        ];

        $urls = [];

        // Extract from img tags
        foreach ($queries as $query) {
            $nodes = $xpath->query($query);
            
            if (! $nodes) {
                continue;
            }

            foreach ($nodes as $node) {
                $src = '';

                if ($node instanceof \DOMElement) {
                    // 1) src روی خود img
                    $src = trim($node->getAttribute('src'));

                    // 2) data-src برای lazy load
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

                    // 5) data-srcset (اولین آدرس در srcset)
                    if ($src === '' || $src === 'data:image' || str_starts_with($src, 'data:')) {
                        $srcset = trim($node->getAttribute('data-srcset') ?? '');
                        if ($srcset !== '') {
                            // srcset مثل "url1 1x, url2 2x" است
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
                } elseif ($node instanceof \DOMAttr) {
                    $src = trim($node->value);
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

        // Extract from source tags (especially webp URLs)
        foreach ($sourceQueries as $query) {
            $nodes = $xpath->query($query);
            
            if (! $nodes) {
                continue;
            }

            foreach ($nodes as $node) {
                if (! ($node instanceof \DOMElement)) {
                    continue;
                }

                // Extract srcset from source tag (check both srcset and data-srcset)
                $srcset = trim($node->getAttribute('srcset') ?? '');
                if ($srcset === '') {
                    $srcset = trim($node->getAttribute('data-srcset') ?? '');
                }
                
                if ($srcset !== '') {
                    // srcset مثل "url1 1x, url2 2x" است
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

                // Extract src from source tag (check both src and data-src)
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

        // Also try to extract from JSON-LD or script tags that might contain image data
        $urls = array_merge($urls, $this->extractFromScriptTags($html, $baseUrl));

        // Also try regex extraction for static.gigabyte.com URLs (in case DOM parsing misses them)
        $regexUrls = $this->extractStaticUrlsWithRegex($html);
        $urls = array_merge($urls, $regexUrls);

        // Make unique and reindex
        $urls = array_values(array_unique($urls));

        return $urls;
    }

    /**
     * Try to extract the URL behind the "Download Images" button/link.
     * Looks for:
     * - <span class="download-images-text">Download Images</span> inside an <a href="...">
     * - any <a> whose text contains "Download Images".
     *
     * @param  string  $html
     * @param  string  $baseUrl
     * @return string|null
     */
    protected function extractDownloadImagesUrl(string $html, string $baseUrl): ?string
    {
        $dom = new \DOMDocument();

        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // 1) span با کلاس download-images-text و والد a
        $nodes = $xpath->query("//span[contains(@class, 'download-images-text')]/ancestor::a[1]");
        if ($nodes && $nodes->length > 0) {
            /** @var \DOMElement $a */
            $a = $nodes->item(0);
            $href = trim($a->getAttribute('href'));
            if ($href !== '') {
                return $this->toAbsoluteUrl($href, $baseUrl);
            }
        }

        // 2) هر a که متنش شامل Download Images باشد
        $nodes = $xpath->query("//a[contains(normalize-space(.), 'Download Images')]");
        if ($nodes && $nodes->length > 0) {
            /** @var \DOMElement $a */
            $a = $nodes->item(0);
            $href = trim($a->getAttribute('href'));
            if ($href !== '') {
                return $this->toAbsoluteUrl($href, $baseUrl);
            }
        }

        return null;
    }

    /**
     * Extract image URLs from script tags (JSON-LD, inline JSON, etc.)
     *
     * @param  string  $html
     * @param  string  $baseUrl
     * @return array<int, string>
     */
    protected function extractFromScriptTags(string $html, string $baseUrl): array
    {
        $urls = [];

        // Look for JSON-LD or inline JSON with image URLs
        if (preg_match_all('#<script[^>]*>(.*?)</script>#is', $html, $matches)) {
            foreach ($matches[1] as $scriptContent) {
                // Try to find JSON objects
                if (preg_match_all('#["\']([^"\']*\.(?:jpe?g|png|webp|gif)[^"\']*)["\']#i', $scriptContent, $imgMatches)) {
                    foreach ($imgMatches[1] as $imgUrl) {
                        $absolute = $this->toAbsoluteUrl($imgUrl, $baseUrl);
                        if ($this->looksLikeImageUrl($absolute) && ! in_array($absolute, $urls, true)) {
                            $urls[] = $absolute;
                        }
                    }
                }
            }
        }

        return $urls;
    }

    /**
     * Extract static.gigabyte.com URLs using regex (for cases where DOM parsing might miss them).
     * Looks for URLs in src, srcset, data-src, data-srcset attributes, JavaScript, and JSON.
     *
     * @param  string  $html
     * @return array<int, string>
     */
    protected function extractStaticUrlsWithRegex(string $html): array
    {
        $urls = [];

        // First, try a very simple pattern: find anything between static.gigabyte.com and /Product/
        // This is more flexible and will catch URLs in any format
        $simplePattern = '#static\.gigabyte\.com[^\s<>"\']*?/Product/[^\s<>"\']*?#i';
        
        $matchCount = preg_match_all($simplePattern, $html, $simpleMatches);
        
        if ($matchCount > 0) {
            foreach ($simpleMatches[0] as $index => $url) {
                $originalUrl = $url;
                $trimmed = trim($url);
                // Remove trailing characters that might be part of JavaScript/JSON syntax
                $trimmed = rtrim($trimmed, '",\'})];');
                
                // Remove query strings
                if (str_contains($trimmed, '?')) {
                    $parts = explode('?', $trimmed, 2);
                    $trimmed = $parts[0];
                }
                
                // Clean up any trailing slashes or invalid characters
                $trimmed = rtrim($trimmed, '/');
                
                // Normalize URL: add https:// if missing
                if (! preg_match('#^https?://#i', $trimmed)) {
                    $trimmed = 'https://' . ltrim($trimmed, '/');
                }
                
                // Always add if it contains /Product (with or without trailing slash)
                $lowerTrimmed = strtolower($trimmed);
                if ($trimmed !== '' && (str_contains($lowerTrimmed, '/product/') || str_contains($lowerTrimmed, '/product')) && ! in_array($trimmed, $urls, true)) {
                    $urls[] = $trimmed;
                }
            }
        }

        // Also try more specific patterns
        $patterns = [
            // Pattern 1: Product URLs with format: .../Product/47498/webp/670
            '#https?://static\.gigabyte\.com/[^\s<>"\']+/Product/[^\s<>"\']+/(?:webp|png|jpg|jpeg)/\d+#i',
            // Pattern 2: Product URLs with image extension
            '#https?://static\.gigabyte\.com/[^\s<>"\']+/Product/[^\s<>"\']+\.(?:jpe?g|png|webp|gif)(?:\?[^\s<>"\']*)?#i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $html, $matches)) {
                foreach ($matches[0] as $url) {
                    $trimmed = trim($url);
                    $trimmed = rtrim($trimmed, '",\'})];');
                    
                    if (str_contains($trimmed, '?')) {
                        $parts = explode('?', $trimmed, 2);
                        $trimmed = $parts[0];
                    }
                    
                    if ($trimmed !== '' && ! in_array($trimmed, $urls, true)) {
                        $urls[] = $trimmed;
                    }
                }
            }
        }

        // Also try to extract from srcset attributes (they might have multiple URLs)
        // Only look for Product URLs in srcset
        $srcsetPattern = '#(?:srcset|data-srcset)=["\']([^"\']*https?://static\.gigabyte\.com[^"\']*Product[^"\']*)["\']#i';
        if (preg_match_all($srcsetPattern, $html, $srcsetMatches)) {
            foreach ($srcsetMatches[1] as $srcset) {
                // srcset can contain multiple URLs separated by commas
                $parts = preg_split('/\s*,\s*/', $srcset);
                foreach ($parts as $part) {
                    // Extract URL (first part before space or descriptor)
                    $urlPart = trim(explode(' ', trim($part))[0]);
                    $urlPart = rtrim($urlPart, '",\'})];');
                    
                    // Only add if it's a Product URL
                    if ($urlPart !== '' && str_contains(strtolower($urlPart), '/product/') && ! in_array($urlPart, $urls, true)) {
                        $urls[] = $urlPart;
                    }
                }
            }
        }


        return $urls;
    }

    /**
     * Extract image URLs from Gigabyte product API JSON response.
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

        $normalized = $this->normalizeGigabyteImageUrl($value);

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
     * Normalize image URLs from Gigabyte:
     * - If already absolute, return as is
     * - If protocol-relative (//...), prefix https:
     * - If path-like starting with /, prefix https://www.gigabyte.com
     */
    protected function normalizeGigabyteImageUrl(string $value): string
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

        // URLs starting with / -> www.gigabyte.com
        if (strpos($trimmed, '/') === 0) {
            return 'https://www.gigabyte.com' . $trimmed;
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
