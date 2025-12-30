<?php

namespace App\Support\GetProductImages;

use Illuminate\Support\Facades\Http;

class GigabyteImageFetcher extends BaseImageFetcher
{
    public static function fetchImageUrls(string $url): array
    {
        try {
            $response = Http::withHeaders(static::getHeaders())
                ->timeout(30)
                ->get($url);

            if (! $response->ok()) {
                return [];
            }

            $contentType = $response->header('Content-Type', '');

            if (str_contains($contentType, 'application/json')) {
                $json = $response->json();
                if (is_array($json)) {
                    return static::extractImageUrlsFromApi($json);
                }
            } else {
                $html = $response->body();
                $allUrls = static::extractImageUrls($html, $url);
                // Filter to only static.gigabyte.com URLs with /product/
                $staticUrls = array_filter($allUrls, function ($url) {
                    $lowerUrl = strtolower($url);
                    return str_contains($lowerUrl, 'static.gigabyte.com') &&
                           (str_contains($lowerUrl, '/product/') || str_contains($lowerUrl, '/product'));
                });

                // Convert to webp with largest size (1200)
                return static::convertToWebpUrls(array_values($staticUrls));
            }
        } catch (\Throwable $e) {
            return [];
        }

        return [];
    }

    protected static function extractImageUrlsFromApi(array $json): array
    {
        $urls = [];
        static::collectUrlsFromArray($json, $urls);
        return array_values(array_unique($urls));
    }

    protected static function collectUrlsFromArray(array $data, array &$urls): void
    {
        foreach ($data as $key => $value) {
            if (is_string($value) && static::looksLikeImageUrl($value)) {
                if (! in_array($value, $urls, true)) {
                    $urls[] = $value;
                }
            } elseif (is_array($value)) {
                static::collectUrlsFromArray($value, $urls);
            }
        }
    }

    protected static function extractImageUrls(string $html, string $baseUrl): array
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $queries = ["//img"];

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

                $src = trim($node->getAttribute('src') ?? '');
                if (empty($src) || $src === 'data:image') {
                    $src = trim($node->getAttribute('data-src') ?? '');
                }

                if ($src === '' || str_starts_with($src, 'data:') || ! static::looksLikeImageUrl($src)) {
                    continue;
                }

                $absolute = static::toAbsoluteUrl($src, $baseUrl);
                if (! in_array($absolute, $urls, true)) {
                    $urls[] = $absolute;
                }
            }
        }

        return array_values(array_unique($urls));
    }

    protected static function convertToWebpUrls(array $urls): array
    {
        $webpUrls = [];
        $largestSize = 1200;

        foreach ($urls as $url) {
            $lowerUrl = strtolower($url);

            if (str_contains($lowerUrl, '.webp') || str_contains($lowerUrl, '/webp/')) {
                if (preg_match('#/webp/(\d+)#i', $url, $sizeMatch)) {
                    $largestSizeUrl = preg_replace('#/webp/\d+#i', "/webp/{$largestSize}", $url);
                    if (! in_array($largestSizeUrl, $webpUrls, true)) {
                        $webpUrls[] = $largestSizeUrl;
                    }
                } else {
                    if (! in_array($url, $webpUrls, true)) {
                        $webpUrls[] = $url;
                    }
                }
            } elseif (str_contains($lowerUrl, '/png/')) {
                $webpUrl = str_replace('/png/', '/webp/', $url);
                $webpUrl = str_replace('.png', '.webp', $webpUrl);
                if (preg_match('#/webp/(\d+)#i', $webpUrl, $sizeMatch)) {
                    $webpUrl = preg_replace('#/webp/\d+#i', "/webp/{$largestSize}", $webpUrl);
                }
                if (! in_array($webpUrl, $webpUrls, true)) {
                    $webpUrls[] = $webpUrl;
                }
            }
        }

        return $webpUrls;
    }
}

