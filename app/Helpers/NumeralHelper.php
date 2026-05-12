<?php

namespace App\Helpers;

class NumeralHelper
{
    /** Arabic-Indic (U+0660–U+0669) and Persian (U+06F0–U+06F9) → Western ASCII digits */
    public static function toWesternDigits(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        static $from = null;
        static $to = null;
        if ($from === null) {
            $from = array_merge(
                ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
                ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹']
            );
            $to = array_merge(
                range('0', '9'),
                range('0', '9')
            );
        }

        return str_replace($from, $to, $value);
    }
}
