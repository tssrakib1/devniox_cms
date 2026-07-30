<?php

namespace App\Enums;

enum LeadType: string
{
    case Contact = 'contact';
    case Demo = 'demo';
    case Quote = 'quote';
}
