<?php

namespace App\Enums;

enum FinanceTransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';
}
