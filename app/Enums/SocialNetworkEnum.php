<?php

namespace App\Enums;

enum SocialNetworkEnum: string
{
    case Telegram = 'telegram';
    case Eitaa = 'eitaa';
    case Bale = 'bale';
    case Rubika = 'rubika';
    case Aparat = 'aparat';
    case Instagram = 'instagram';
    case Whatsapp = 'whatsapp';
    case X = 'x';
    case Facebook = 'facebook';
    case Linkedin = 'linkedin';
    case Youtube = 'youtube';

    public function label(): string
    {
        return __('general.social_'.$this->value);
    }

    public function icon(): string
    {
        return match ($this) {
            self::Telegram => 'send',
            self::Whatsapp => 'message-circle',
            self::Youtube, self::Aparat => 'play',
            default => 'link',
        };
    }

    public function isIranian(): bool
    {
        return in_array($this, [
            self::Telegram,
            self::Eitaa,
            self::Bale,
            self::Rubika,
            self::Aparat,
        ], true);
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
