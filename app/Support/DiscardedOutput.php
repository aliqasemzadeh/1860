<?php

namespace App\Support;

final class DiscardedOutput
{
    /**
     * Run a callback while discarding any echoed stdout (e.g. Larascraper debug logs)
     * so sync Livewire/HTTP responses are not corrupted.
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public static function run(callable $callback): mixed
    {
        ob_start();

        try {
            return $callback();
        } finally {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
        }
    }
}
