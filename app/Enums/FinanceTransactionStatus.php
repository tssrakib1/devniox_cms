<?php

namespace App\Enums;

enum FinanceTransactionStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
