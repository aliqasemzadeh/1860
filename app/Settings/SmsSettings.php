<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SmsSettings extends Settings
{
    public string $token;

    public string $gateway;

    public static function group(): string
    {
        return 'sms';
    }
}
