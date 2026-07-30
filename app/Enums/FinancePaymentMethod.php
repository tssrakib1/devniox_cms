<?php

namespace App\Enums;

enum FinancePaymentMethod: string
{
    case Cash = 'cash';
    case Bank = 'bank';
    case MobileBanking = 'mobile_banking';
    case Card = 'card';
    case Cheque = 'cheque';
    case Other = 'other';
}
