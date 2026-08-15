<?php

namespace App\Support;

class SystemCommandGuard
{
    public static function isAllowed(string $command): bool
    {
        $command = trim($command);

        if ($command === '' || self::containsShellMetacharacters($command)) {
            return false;
        }

        $base = explode(' ', $command, 2)[0];

        foreach (config('system-commands.blocked', []) as $blocked) {
            if (str_ends_with($blocked, ':')) {
                if (str_starts_with($base, $blocked)) {
                    return false;
                }

                continue;
            }

            if ($base === $blocked || str_starts_with($base, $blocked.':')) {
                return false;
            }
        }

        return in_array($base, config('system-commands.allowed', []), true);
    }

    public static function containsShellMetacharacters(string $command): bool
    {
        return (bool) preg_match('/[;&|`$<>\\\\]|\n|\r/', $command);
    }

    public static function stripAnsi(?string $output): string
    {
        if ($output === null || $output === '') {
            return '';
        }

        $output = preg_replace('/\e\[[\d;]*[A-Za-z]/', '', $output) ?? $output;
        $output = preg_replace('/\x1b\[[0-9;]*[a-zA-Z]/', '', $output) ?? $output;

        return trim($output);
    }
}
