<?php

namespace App\Enums;

/** What the UI shows for an item; mirrors resources/js/types/status.ts. */
enum DisplayStatus: string
{
    case Proposed = 'proposed';
    case Confirmed = 'confirmed';
    case Urgent = 'urgent';
    case Recorded = 'recorded';
    case Fulfilled = 'fulfilled';
}
