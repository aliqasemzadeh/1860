<?php

namespace App\Support\GetProductImages;

class XVisionImageFetcher extends BaseImageFetcher
{
    public static function fetchImageUrls(string $url): array
    {
        $html = static::fetchHtml($url);
        if (! $html) {
            return [];
        }

        $allUrls = static::extractImageUrls($html, $url);
        return static::filterProductImages($allUrls);
    }

    protected static function extractImageUrls(string $html, string $baseUrl): array
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $queries = [
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

                $src = trim($node->getAttribute('src') ?? '');
                if (empty($src) || $src === 'data:image') {
                    $src = trim($node->getAttribute('data-src') ?? '');
                }
                if (empty($src) || $src === 'data:image') {
                    $src = trim($node->getAttribute('data-lazy-src') ?? '');
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

    protected static function filterProductImages(array $urls): array
    {
        $productUrls = [];
        $excludePatterns = ['#logo#i', '#icon#i', '#banner#i', '#header#i', '#footer#i'];

        foreach ($urls as $url) {
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

            // Include URLs from uploads directory (product images)
            if (preg_match('#/uploads/#i', $url) && static::looksLikeImageUrl($url)) {
                $productUrls[] = $url;
            }
        }

        return $productUrls;
    }
}

