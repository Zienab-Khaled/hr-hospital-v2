<?php

use App\Helpers\NumeralHelper;

if (!function_exists('western_digits')) {
    /** Normalize Arabic-Indic / Persian digits to Western (0–9). */
    function western_digits(?string $value): string
    {
        return NumeralHelper::toWesternDigits($value);
    }
}
