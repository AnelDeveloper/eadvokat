<?php

namespace App\Enums;

enum ReminderKind: string
{
    case DeadlineThreeDays = 'deadline_3';
    case DeadlineOneDay = 'deadline_1';
    case FollowUp = 'follow_up';
    case Manual = 'manual';
}
