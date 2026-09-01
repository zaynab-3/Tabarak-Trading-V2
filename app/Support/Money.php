<?php

namespace App\Support;

final class Money
{
    public const CURRENCY = 'USD';

    public static function toCents(string|int|float $amount): int
    {
        $decimal = number_format((float) $amount, 2, '.', '');
        [$whole, $fraction] = explode('.', $decimal);

        return ((int) $whole * 100) + (int) $fraction;
    }

    public static function fromCents(int $amount): string
    {
        return number_format($amount / 100, 2, '.', '');
    }
}
