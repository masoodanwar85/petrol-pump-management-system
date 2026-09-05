<?php

namespace App\Enums;

enum NozzleSide: string
{
    case A = 'A';
    case B = 'B';

    public function number(): int
    {
        return match ($this) {
            self::A => 1,
            self::B => 2,
        };
    }

    public function label(): string
    {
        return 'Nozzle '.$this->number();
    }
}
