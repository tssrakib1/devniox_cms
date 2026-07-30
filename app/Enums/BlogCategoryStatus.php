<?php

namespace App\Enums;

enum BlogCategoryStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
}
