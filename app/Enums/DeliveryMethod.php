<?php

namespace App\Enums;

enum DeliveryMethod: string
{
    case InPerson = 'in_person';
    case RegisteredMail = 'registered_mail';

    /** "Lično na pisarnici" */
    public function label(): string
    {
        return __('enums.delivery_method.'.$this->value);
    }
}
