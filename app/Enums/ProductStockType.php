<?php

namespace App\Enums;

enum ProductStockType: string
{
    case Purchase = 'purchase';
    case Adjustment = 'adjustment';
}
