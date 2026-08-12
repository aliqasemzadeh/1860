<?php

use Morilog\Jalali\Jalalian;

if (! function_exists('jalali')) {
    function jalali(?\DateTimeInterface $date, string $format = 'Y/m/d H:i', string $empty = '-'): string
    {
        if ($date === null) {
            return $empty;
        }

        return Jalalian::forge($date)->format($format);
    }
}
