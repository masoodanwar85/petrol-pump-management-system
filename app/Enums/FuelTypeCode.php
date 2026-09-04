<?php

namespace App\Enums;

enum FuelTypeCode: string
{
    case Petrol = 'petrol';
    case Diesel = 'diesel';

    public function label(): string
    {
        return match ($this) {
            self::Petrol => 'Petrol',
            self::Diesel => 'Diesel',
        };
    }
}
