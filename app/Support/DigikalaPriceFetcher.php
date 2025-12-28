<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

class DigikalaPriceFetcher
{
    public static function fetchPrice(string $url): ?int
    {
        // Extract product ID from URL
        if (!preg_match('/dkp-(\d+)/', $url, $matches)) {
            return null;
        }

        $productId = $matches[1];

        // Try multiple methods to fetch price
        $price = self::tryApiMethod($productId);
        if ($price) {
            return $price;
        }

        $price = self::tryWebApiMethod($productId);
        if ($price) {
            return $price;
        }

        $price = self::tryHtmlScraping($productId);
        if ($price) {
            return $price;
        }

        return null;
    }

    private static function tryApiMethod(string $productId): ?int
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'application/json',
            ])->timeout(10)->get("https://api.digikala.com/v1/product/{$productId}/");

            if ($response->successful()) {
                $data = $response->json();
                $price = $data['data']['product']['default_variant']['price']['selling_price'] ??
                    $data['data']['product']['price']['selling_price'] ?? null;
                if ($price) {
                    return (int) $price;
                }
            }
        } catch (\Exception $e) {
            // Silent fail
        }

        return null;
    }

    private static function tryWebApiMethod(string $productId): ?int
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'application/json',
            ])->timeout(10)->get("https://www.digikala.com/api/v1/product/{$productId}/");

            if ($response->successful()) {
                $data = $response->json();
                $price = $data['data']['product']['default_variant']['price']['selling_price'] ??
                    $data['data']['product']['price']['selling_price'] ?? null;
                if ($price) {
                    return (int) $price;
                }
            }
        } catch (\Exception $e) {
            // Silent fail
        }

        return null;
    }

    private static function tryHtmlScraping(string $productId): ?int
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'text/html',
            ])->timeout(10)->get("https://www.digikala.com/product/dkp-{$productId}/");

            if ($response->successful()) {
                $html = $response->body();

                // Look for price patterns
                if (preg_match('/"selling_price"\s*:\s*(\d+)/', $html, $matches)) {
                    return (int) $matches[1];
                }
            }
        } catch (\Exception $e) {
            // Silent fail
        }

        return null;
    }
}

