<?php

namespace App\Enums;

enum CustomerTransactionType: string
{
    case Sale = 'sale';
    case Payment = 'payment';
}
