<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SocialSettings extends Settings
{
    public array $links;

    public static function group(): string
    {
        return 'social';
    }

    public function active(): array
    {
        return array_filter(
            $this->links,
            fn ($url) => filled($url)
        );
    }
}
