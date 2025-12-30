<?php

namespace App\Support\GetProductImages;

class LogikeyImageFetcher extends BaseImageFetcher
{
    public static function fetchImageUrls(string $url): array
    {
        $html = static::fetchHtml($url);
        if (! $html) {
            return [];
        }

        return static::extractImageUrls($html, $url);
    }

    protected static function extractImageUrls(string $html, string $baseUrl): array
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $productGalleryQuery = "//*[contains(@class, 'woocommerce-product-gallery')]//img[@src or @data-src]";

        $urls = [];
        $webpUrls = [];
        $otherUrls = [];

        $nodes = $xpath->query($productGalleryQuery);
        if ($nodes) {
            foreach ($nodes as $node) {
                /** @var \DOMElement $node */
                $src = trim($node->getAttribute('src') ?? '');

                if (empty($src) || $src === 'data:image') {
                    $src = trim($node->getAttribute('data-src') ?? '');
                }
                if (empty($src) || $src === 'data:image') {
                    $src = trim($node->getAttribute('data-large_image') ?? '');
                }

                if ($src === '' || $src === 'data:image') {
                    continue;
                }

                $absolute = static::toAbsoluteUrl($src, $baseUrl);

                if (str_ends_with(strtolower($absolute), '.webp')) {
                    if (! in_array($absolute, $webpUrls, true)) {
                        $webpUrls[] = $absolute;
                    }
                } else {
                    $webpUrl = static::getWebpUrl($absolute);
                    if ($webpUrl && $webpUrl !== $absolute) {
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

        return array_merge($webpUrls, $otherUrls);
    }

    protected static function getWebpUrl(string $url): ?string
    {
        if (str_ends_with(strtolower($url), '.webp')) {
            return $url;
        }

        $patterns = [
            '/\.(jpg|jpeg|png|gif)(\?.*)?$/i' => '.webp$2',
            '/\.(jpg|jpeg|png|gif)$/i' => '.webp',
        ];

        foreach ($patterns as $pattern => $replacement) {
            if (preg_match($pattern, $url)) {
                return preg_replace($pattern, $replacement, $url);
            }
        }

        return null;
    }
}

