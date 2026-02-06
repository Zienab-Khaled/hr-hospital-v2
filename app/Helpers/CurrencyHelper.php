<?php

namespace App\Helpers;

class CurrencyHelper
{
    public static function symbol(): string
    {
        return 'ريال';
    }

    public static function format(float|string $amount, bool $withSymbol = true): string
    {
        $formatted = number_format((float) $amount, 2);
        return $withSymbol ? $formatted . ' ' . self::symbol() : $formatted;
    }
}
