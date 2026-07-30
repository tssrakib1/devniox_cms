<?php

namespace App\Enums;

enum FinanceTransactionSource: string
{
    case Manual = 'manual';
    case Order = 'order';
    case Hosting = 'hosting';
    case Domain = 'domain';
    case Maintenance = 'maintenance';
    case Other = 'other';
}
