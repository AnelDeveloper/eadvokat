<?php

namespace App\Enums;

/** Status of mail entries, events, notes and cost items. */
enum ItemStatus: string
{
    case Proposed = 'proposed';
    case Confirmed = 'confirmed';
}
