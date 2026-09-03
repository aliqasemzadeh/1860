<?php

namespace App\Support;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\Events\JobAttempted;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Log;
use ReflectionObject;
use Throwable;

class JobLogger
{
    private const int MAX_OUTPUT_LENGTH = 10000;

    /** @var array<string, float> */
    private static array $timers = [];

    /** @var array<string, array{class: string, message: string}> */
    private static array $exceptions = [];

    /** @var list<string> */
    private const array SENSITIVE_KEYS = [
        'password',
        'secret',
        'token',
        'key',
        'otp',
        'mobile',
        'phone',
    ];

    public static function processing(JobProcessing $event): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $uuid = self::jobUuid($event->job);

        self::$timers[$uuid] = microtime(true);

        Log::channel('jobs')->info('Job started', self::baseContext($event->job, $event->connectionName, [
            'payload' => self::extractPayload($event->job),
        ]));
    }

    public static function exceptionOccurred(JobExceptionOccurred $event): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $uuid = self::jobUuid($event->job);

        self::$exceptions[$uuid] = [
            'class' => $event->exception::class,
            'message' => self::truncate($event->exception->getMessage()),
        ];
    }

    public static function attempted(JobAttempted $event): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $uuid = self::jobUuid($event->job);
        $startedAt = self::$timers[$uuid] ?? microtime(true);
        $exception = self::$exceptions[$uuid] ?? null;
        $successful = $event->successful();

        $context = self::baseContext($event->job, $event->connectionName, [
            'status' => $successful ? 'success' : 'failed',
            'duration_ms' => self::durationMs($startedAt),
            'attempts' => $event->job->attempts(),
        ]);

        if ($exception !== null) {
            $context['exception'] = $exception;
        }

        unset(self::$timers[$uuid], self::$exceptions[$uuid]);

        Log::channel('jobs')->{$successful ? 'info' : 'error'}('Job finished', $context);
    }

    /**
     * @return array<string, mixed>
     */
    private static function extractPayload(Job $job): array
    {
        $payload = $job->payload();
        $context = [
            'display_name' => $payload['displayName'] ?? $job->resolveName(),
        ];

        $serialized = $payload['data']['command'] ?? null;

        if (! is_string($serialized)) {
            return $context;
        }

        try {
            $instance = unserialize($serialized, ['allowed_classes' => true]);

            if (! is_object($instance)) {
                return $context;
            }

            $properties = [];

            foreach ((new ReflectionObject($instance))->getProperties() as $property) {
                if (! $property->isPublic()) {
                    continue;
                }

                $name = $property->getName();
                $value = $property->getValue($instance);

                if ($value instanceof Model) {
                    $value = ['model' => $value::class, 'id' => $value->getKey()];
                }

                $properties[$name] = self::sanitizeValue($name, $value);
            }

            if ($properties !== []) {
                $context['properties'] = $properties;
            }
        } catch (Throwable) {
            $context['properties'] = ['_error' => 'unable to deserialize payload'];
        }

        return $context;
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private static function baseContext(Job $job, string $connection, array $extra = []): array
    {
        return array_merge([
            'job' => $job->resolveName(),
            'uuid' => self::jobUuid($job),
            'queue' => $job->getQueue(),
            'connection' => $connection,
            'environment' => app()->environment(),
        ], $extra);
    }

    private static function jobUuid(Job $job): string
    {
        return $job->uuid() ?? spl_object_hash($job);
    }

    private static function sanitizeValue(string $key, mixed $value): mixed
    {
        if (self::isSensitiveKey($key)) {
            if ($key === 'mobile' || $key === 'phone') {
                return self::maskMobile(is_string($value) ? $value : '***');
            }

            return '***';
        }

        if (is_array($value)) {
            $sanitized = [];

            foreach ($value as $nestedKey => $nestedValue) {
                $sanitized[$nestedKey] = self::sanitizeValue((string) $nestedKey, $nestedValue);
            }

            return $sanitized;
        }

        if (is_object($value)) {
            if ($value instanceof Model) {
                return ['model' => $value::class, 'id' => $value->getKey()];
            }

            return $value::class;
        }

        if (is_string($value) && strlen($value) > 500) {
            return self::truncate($value);
        }

        return $value;
    }

    private static function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower($key);

        foreach (self::SENSITIVE_KEYS as $sensitive) {
            if (str_contains($normalized, $sensitive)) {
                return true;
            }
        }

        return false;
    }

    private static function maskMobile(string $mobile): string
    {
        $digits = preg_replace('/\D+/', '', $mobile) ?? $mobile;

        if (strlen($digits) < 4) {
            return '***';
        }

        return substr($digits, 0, 4).'***'.substr($digits, -4);
    }

    private static function truncate(string $value): string
    {
        if (strlen($value) <= self::MAX_OUTPUT_LENGTH) {
            return $value;
        }

        return substr($value, 0, self::MAX_OUTPUT_LENGTH).' [truncated]';
    }

    private static function durationMs(float $startedAt): int
    {
        return (int) ((microtime(true) - $startedAt) * 1000);
    }
}
