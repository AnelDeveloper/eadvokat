<?php

namespace App\Enums;

enum MailDirection: string
{
    case In = 'in';
    case Out = 'out';

    public function prefix(): string
    {
        return $this === self::In ? 'U' : 'I';
    }

    /** Timeline type label: "Ulazna pošta" / "Izlazna pošta". */
    public function label(): string
    {
        return __('enums.mail_direction.'.$this->value);
    }
}
