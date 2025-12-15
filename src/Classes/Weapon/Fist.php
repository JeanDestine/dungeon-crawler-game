<?php

namespace App\Classes\Weapon;

use App\Classes\Weapon;
use App\Enums\Weapon\Type;

class Fist extends Weapon
{
    public function __construct()
    {
        parent::__construct(
            ucfirst(Type::FISTS->value),
            10,
            Type::FISTS
        );
    }
}
