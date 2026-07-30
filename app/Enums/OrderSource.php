<?php

namespace App\Enums;

enum OrderSource: string
{
    case Lead = 'lead';
    case Direct = 'direct';
}
