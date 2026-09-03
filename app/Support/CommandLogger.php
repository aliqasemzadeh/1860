<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Throwable;

class CommandLogger
{
    private const int MAX_OUTPUT_LENGTH = 10000;

    /** @var list<string> */
    private const array SKIPPED_COMMANDS = [
        'help',
        'list',
        'completion',
        '_complete',
    ];

    /** @var list<string> */
    private const array SENSITIVE_KEYS = [
        'password',
        'secret',
        'token',
        'key',
        'otp',
    ];

    public static function shouldSkip(Command $command): bool
    {
        if (app()->runningUnitTests()) {
            return true;
        }

        $name = $command->getName() ?? '';

        return in_array($name, self::SKIPPED_COMMANDS, true);
    }

    public static function started(string $command, InputInterface $input): void
    {
        Log::channel('commands')->info('Command started', self::baseContext($command, [
            'arguments' => self::sanitizeArray($input->getArguments()),
            'options' => self::sanitizeArray($input->getOptions()),
        ]));
    }

    public static function finished(
        string $command,
        InputInterface $input,
        string $output,
        int $exitCode,
        float $startedAt,
    ): void {
        $level = $exitCode === 0 ? 'info' : 'error';

        Log::channel('commands')->{$level}('Command finished', self::baseContext($command, [
            'arguments' => self::sanitizeArray($input->getArguments()),
            'options' => self::sanitizeArray($input->getOptions()),
            'exit_code' => $exitCode,
            'duration_ms' => self::durationMs($startedAt),
            'output' => self::truncate(SystemCommandGuard::stripAnsi($output)),
        ]));
    }

    public static function failed(
        string $command,
        InputInterface $input,
        string $output,
        float $startedAt,
        Throwable $exception,
    ): void {
        Log::channel('commands')->error('Command failed', self::baseContext($command, [
            'arguments' => self::sanitizeArray($input->getArguments()),
            'options' => self::sanitizeArray($input->getOptions()),
            'duration_ms' => self::durationMs($startedAt),
            'output' => self::truncate(SystemCommandGuard::stripAnsi($output)),
            'exception' => $exception::class,
            'message' => self::truncate($exception->getMessage()),
        ]));
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private static function baseContext(string $command, array $extra = []): array
    {
        return array_merge([
            'command' => $command,
            'environment' => app()->environment(),
            'pid' => getmypid(),
        ], $extra);
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private static function sanitizeArray(array $values): array
    {
        $sanitized = [];

        foreach ($values as $key => $value) {
            $sanitized[$key] = self::sanitizeValue((string) $key, $value);
        }

        return $sanitized;
    }

    private static function sanitizeValue(string $key, mixed $value): mixed
    {
        if (self::isSensitiveKey($key)) {
            return '***';
        }

        if (is_array($value)) {
            return self::sanitizeArray($value);
        }

        if (is_object($value)) {
            if (method_exists($value, 'getKey')) {
                return ['id' => $value->getKey()];
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
