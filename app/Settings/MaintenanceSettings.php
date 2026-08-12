<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MaintenanceSettings extends Settings
{
    public string $message;

    public ?string $secret;

    public ?int $retry;

    public ?int $refresh;

    public static function group(): string
    {
        return 'maintenance';
    }

    /**
     * @return list<string>
     */
    public static function encrypted(): array
    {
        return [
            'secret',
        ];
    }
}
