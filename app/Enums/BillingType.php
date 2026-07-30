<?php

namespace App\Enums;

enum BillingType: string
{
    case OneTime = 'one_time';
    case Monthly = 'monthly';
    case Yearly = 'yearly';
    case Custom = 'custom';
}
