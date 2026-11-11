<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

final class FetcherLog
{
    public static function channel(): LoggerInterface
    {
        return Log::channel('fetchers');
    }

    /** @param  array<string, mixed>  $context */
    public static function info(string $message, array $context = []): void
    {
        self::channel()->info($message, $context);
    }

    /** @param  array<string, mixed>  $context */
    public static function warning(string $message, array $context = []): void
    {
        self::channel()->warning($message, $context);
    }

    /** @param  array<string, mixed>  $context */
    public static function error(string $message, array $context = []): void
    {
        self::channel()->error($message, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public static function decision(
        string $source,
        string $outcome,
        string $reason,
        array $context = [],
    ): array {
        return array_merge([
            'source' => $source,
            'outcome' => $outcome,
            'reason' => $reason,
        ], $context);
    }
}
