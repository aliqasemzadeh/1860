<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TorobChallengeGuard
{
    public const CACHE_KEY = 'torob:challenge:blocked-until';

    public function ensureRequestsAllowed(): void
    {
        $retryAt = (int) Cache::get(self::CACHE_KEY, 0);

        if ($retryAt <= now()->timestamp) {
            if ($retryAt > 0) {
                Cache::forget(self::CACHE_KEY);
            }

            return;
        }

        throw new TorobChallengeException($retryAt);
    }

    public function activate(): TorobChallengeException
    {
        $seconds = max(1, (int) config('services.torob.challenge_cooldown_seconds', 3600));
        $retryAt = now()->addSeconds($seconds)->timestamp;
        $activated = Cache::add(self::CACHE_KEY, $retryAt, $seconds);

        if (! $activated) {
            $retryAt = max($retryAt, (int) Cache::get(self::CACHE_KEY, $retryAt));
        } else {
            Log::warning('Torob ARCaptcha challenge detected; requests paused.', [
                'host' => 'torob.com',
                'blocked_until' => now()->setTimestamp($retryAt)->toIso8601String(),
            ]);
        }

        return new TorobChallengeException($retryAt);
    }
}
