<?php

namespace App\Enums;

enum OrderItemType: string
{
    case Software = 'software';
    case Website = 'website';
    case MobileApp = 'mobile_app';
    case Hosting = 'hosting';
    case Domain = 'domain';
    case CustomDevelopment = 'custom_development';
    case Other = 'other';
}
