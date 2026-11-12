<?php

namespace App\Enums;

enum Jurisdiction: string
{
    case Fbih = 'fbih';
    case Rs = 'rs';
    case Brcko = 'brcko';

    /** "FBiH", "Republika Srpska", "Brčko distrikt" */
    public function label(): string
    {
        return __('enums.jurisdiction.'.$this->value);
    }

    /** Button label: "FBiH", "RS", "Brčko distrikt" */
    public function shortLabel(): string
    {
        return __('enums.jurisdiction_short.'.$this->value);
    }
}
