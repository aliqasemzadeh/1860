<?php

namespace App\Support\GetProductImages;

class GreenImageFetcher extends BaseImageFetcher
{
    public static function fetchImageUrls(string $url): array
    {
        $html = static::fetchHtml($url);
        if (! $html) {
            return [];
        }

        $allUrls = static::extractImageUrls($html, $url);
        return static::filterGalleryImages($allUrls);
    }

    protected static function extractImageUrls(string $html, string $baseUrl): array
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $queries = [
            "//*[contains(@class, 'single-product-carousel')]//img",
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

    protected static function filterGalleryImages(array $urls): array
    {
        // Filter to only URLs with 375x375 size (gallery images)
        return array_filter($urls, function ($url) {
            return preg_match('#375x375#i', $url) && static::looksLikeImageUrl($url);
        });
    }
}

