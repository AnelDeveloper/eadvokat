<?php

namespace App\Enums;

enum EventType: string
{
    case Hearing = 'hearing';
    case Meeting = 'meeting';
    case CourtVisit = 'court_visit';

    public function label(): string
    {
        return __('enums.event_type.'.$this->value);
    }
}
