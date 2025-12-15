<?php

namespace App\Classes\Weapon;

use App\Classes\Weapon;
use App\Enums\Weapon\Type;

class Bat extends Weapon
{
    public function __construct()
    {
        parent::__construct(
            ucfirst(Type::BAT->value),
            25,
            Type::BAT
        );
    }
}
