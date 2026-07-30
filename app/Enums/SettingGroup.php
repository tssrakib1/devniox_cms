<?php

namespace App\Enums;

enum SettingGroup: string
{
    case Company = 'company';
    case Branding = 'branding';
    case Contact = 'contact';
    case Hours = 'hours';
    case Social = 'social';
    case Seo = 'seo';
    case Mail = 'mail';
    case Analytics = 'analytics';
    case General = 'general';
    case Email = 'email';
    case Integrations = 'integrations';
    case Maintenance = 'maintenance';
}
