<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use RuntimeException;

class TorobChallengeException extends RuntimeException
{
    public function __construct(public readonly int $retryAtTimestamp)
    {
        $retryAt = CarbonImmutable::createFromTimestamp($retryAtTimestamp)
            ->setTimezone((string) config('app.timezone'));

        parent::__construct(sprintf(
            'Torob returned an ARCaptcha challenge. Requests are paused until %s.',
            $retryAt->toIso8601String(),
        ));
    }
}
