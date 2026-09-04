<?php

namespace App\Enums;

enum ExpenseCategory: string
{
    case Salary = 'salary';
    case Utility = 'utility';
    case Misc = 'misc';
}
