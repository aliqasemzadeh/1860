<?php

namespace App\Support\GetProductImages;

use Illuminate\Support\Facades\Http;

class FaterImageFetcher extends BaseImageFetcher
{
    public static function fetchImageUrls(string $url): array
    {
        $apiUrl = static::buildApiUrl($url);

        try {
            $response = Http::withHeaders(static::getHeaders())
                ->timeout(30)
                ->get($apiUrl);

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
                return static::extractImageUrls($html, $url);
            }
        } catch (\Throwable $e) {
            return [];
        }

        return [];
    }

    protected static function buildApiUrl(string $url): string
    {
        if (str_contains($url, 'api.faterco.ir/api/v1/Product/GetProductDetail')) {
            return $url;
        }

        if (preg_match('#faterco\.ir/(?:product|products)/([^/]+)#i', $url, $matches)) {
            $slug = $matches[1];
            return "https://api.faterco.ir/api/v1/Product/GetProductDetail?slug={$slug}";
        }

        return $url;
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
        $queries = [
            "//*[contains(@class, 'swiper')]//img",
            "//*[contains(@class, 'product-gallery')]//img",
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
}

