<?php

namespace App\Classes\Weapon;

use App\Classes\Weapon;
use App\Enums\Weapon\Type;

class Sword extends Weapon
{
    public function __construct()
    {
        parent::__construct(
            ucfirst(Type::SWORD->value),
            50,
            Type::SWORD
        );
    }
}
