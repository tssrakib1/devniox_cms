<?php

namespace App\Enums;

enum LeadItemType: string
{
    case Product = 'product';
    case Service = 'service';
    case Portfolio = 'portfolio';
}
