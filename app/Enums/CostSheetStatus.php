<?php

namespace App\Enums;

enum CostSheetStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';
}
