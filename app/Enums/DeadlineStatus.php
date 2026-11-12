<?php

namespace App\Enums;

enum DeadlineStatus: string
{
    case Proposed = 'proposed';
    case Confirmed = 'confirmed';
    case Fulfilled = 'fulfilled';
}
