<?php

namespace App\Enums;

enum TankTransactionType: string
{
    case Purchase = 'purchase';
    case Wastage = 'wastage';
    case Adjustment = 'adjustment';
}
