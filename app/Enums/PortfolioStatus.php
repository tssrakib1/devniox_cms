<?php

namespace App\Enums;

enum PortfolioStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
