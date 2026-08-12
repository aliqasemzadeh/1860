<?php

namespace App\Settings;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $title;

    public string $description;

    public string $keywords;

    public ?string $favicon_path;

    public ?string $logo_path;

    public static function group(): string
    {
        return 'general';
    }

    public function faviconUrl(): ?string
    {
        return $this->favicon_path
            ? Storage::disk('public')->url($this->favicon_path)
            : null;
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path
            ? Storage::disk('public')->url($this->logo_path)
            : null;
    }
}
