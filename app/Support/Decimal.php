<?php

namespace App\Support;

final class Decimal
{
    public static function of(int|float|string|null $value, int $scale = 3): string
    {
        return bcadd(self::normalize($value), '0', $scale);
    }

    public static function add(int|float|string $left, int|float|string $right, int $scale = 3): string
    {
        return bcadd(self::normalize($left), self::normalize($right), $scale);
    }

    public static function subtract(int|float|string $left, int|float|string $right, int $scale = 3): string
    {
        return bcsub(self::normalize($left), self::normalize($right), $scale);
    }

    public static function multiply(int|float|string $left, int|float|string $right, int $scale = 3): string
    {
        return bcmul(self::normalize($left), self::normalize($right), $scale);
    }

    public static function divide(int|float|string $left, int|float|string $right, int $scale = 3): string
    {
        return bcdiv(self::normalize($left), self::normalize($right), $scale);
    }

    public static function compare(int|float|string $left, int|float|string $right, int $scale = 3): int
    {
        return bccomp(self::normalize($left), self::normalize($right), $scale);
    }

    public static function isNegative(int|float|string $value, int $scale = 3): bool
    {
        return self::compare($value, '0', $scale) < 0;
    }

    public static function isZero(int|float|string $value, int $scale = 3): bool
    {
        return self::compare($value, '0', $scale) === 0;
    }

    public static function money(int|float|string $value): string
    {
        return self::of($value, 2);
    }

    private static function normalize(int|float|string|null $value): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        return is_string($value) ? $value : number_format((float) $value, 6, '.', '');
    }
}
