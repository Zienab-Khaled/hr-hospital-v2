<?php

namespace App\Helpers;

class CurrencyHelper
{
    public static function symbol(): string
    {
        return 'ريال';
    }

    private static function normalizeAmount(float|string|null $amount): float
    {
        if ($amount === null || $amount === '') {
            return 0.0;
        }

        return (float) $amount;
    }

    public static function format(float|string|null $amount, bool $withSymbol = true): string
    {
        $formatted = number_format(self::normalizeAmount($amount), 2);
        return $withSymbol ? $formatted . ' ' . self::symbol() : $formatted;
    }

    /** أرقام لاتينية (0–9) للعرض في الفواتير ولو كانت واجهة النظام عربية */
    public static function formatAmountDecimal(float|string|null $amount, int $decimals = 2): string
    {
        return NumeralHelper::toWesternDigits(number_format(self::normalizeAmount($amount), $decimals, '.', ','));
    }

    /** مثل format مع أرقام لاتينية — للفواتير والطباعة */
    public static function formatInvoice(float|string|null $amount, bool $withSymbol = true): string
    {
        $core = self::formatAmountDecimal($amount, 2);
        return $withSymbol ? $core . ' ' . self::symbol() : $core;
    }

    /**
     * Convert amount to Arabic words for official receipts (تفقيط - Saudi Riyal).
     * Returns e.g. "اثنان وأربعون ألف وخمس مائة وثمانون ريال فقط لاغير"
     */
    public static function amountInArabicWords(float|string|null $amount): string
    {
        $n = self::normalizeAmount($amount);
        $intPart = (int) floor($n);
        $decPart = (int) round(($n - $intPart) * 100);

        $words = self::intToArabicWords($intPart);
        $str = $words . ' ريال';
        if ($decPart > 0) {
            $str .= ' و' . self::intToArabicWords($decPart) . ' هللة';
        }
        return $str . ' فقط لاغير';
    }

    private static function intToArabicWords(int $n): string
    {
        if ($n === 0) {
            return 'صفر';
        }

        $ones = ['', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة'];
        $onesFem = ['', 'واحدة', 'اثنتان', 'ثلاث', 'أربع', 'خمس', 'ست', 'سبع', 'ثمان', 'تسع'];
        $tens = ['', 'عشرة', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'];
        $tensFrom11 = ['عشر', 'إحدى عشرة', 'اثنتا عشرة', 'ثلاث عشرة', 'أربع عشرة', 'خمس عشرة', 'ست عشرة', 'سبع عشرة', 'ثمان عشرة', 'تسع عشرة'];
        $hundreds = ['', 'مائة', 'مائتان', 'ثلاث مائة', 'أربع مائة', 'خمس مائة', 'ست مائة', 'سبع مائة', 'ثمان مائة', 'تسع مائة'];

        $result = '';

        if ($n >= 1_000_000) {
            $millions = (int) floor($n / 1_000_000);
            $result .= self::intToArabicWords($millions);
            $result .= $millions === 2 ? ' مليونا' : ($millions >= 3 && $millions <= 10 ? ' ملايين' : ' مليون');
            if ($millions > 10) {
                $result .= ' و';
            }
            $n %= 1_000_000;
            if ($n > 0) {
                $result .= ' و';
            }
        }

        if ($n >= 1000) {
            $thousands = (int) floor($n / 1000);
            if ($thousands === 1) {
                $result .= 'ألف';
            } elseif ($thousands === 2) {
                $result .= 'ألفان';
            } elseif ($thousands >= 3 && $thousands <= 10) {
                $result .= self::intToArabicWords($thousands) . ' آلاف';
            } else {
                $result .= self::intToArabicWords($thousands) . ' ألف';
            }
            $n %= 1000;
            if ($n > 0) {
                $result .= ' و';
            }
        }

        if ($n >= 100) {
            $h = (int) floor($n / 100);
            $result .= $hundreds[$h];
            $n %= 100;
            if ($n > 0) {
                $result .= ' و';
            }
        }

        if ($n >= 20) {
            $t = (int) floor($n / 10);
            $o = $n % 10;
            if ($o > 0) {
                $result .= $ones[$o] . ' و' . $tens[$t];
            } else {
                $result .= $tens[$t];
            }
        } elseif ($n >= 11 && $n <= 19) {
            $result .= $tensFrom11[$n - 10];
        } elseif ($n >= 10) {
            $result .= 'عشرة';
        } elseif ($n >= 1) {
            $result .= $ones[$n];
        }

        return trim(preg_replace('/\s+/', ' ', $result));
    }
}
