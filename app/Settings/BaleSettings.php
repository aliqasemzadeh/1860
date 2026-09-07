<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class BaleSettings extends Settings
{
    public string $bot_username;

    public string $bot_token;

    public string $chat_id;

    public static function group(): string
    {
        return 'bale';
    }

    /**
     * @return list<string>
     */
    public static function encrypted(): array
    {
        return [
            'bot_token',
        ];
    }
}
