<?php

namespace App\Classes;

use App\Enums\Character\Type;
use InvalidArgumentException;

class Character
{
    public function __construct(
        public string $name,
        public Type $type,
        public int $health = 100
    ) {
        if($health <= 0){
            $health = 0;
        }
    }

    public function isDead(): bool
    {
        return $this->health <= 0;
    }

    public function takeDamage(int $damage): void
    {
        $this->health -= $damage;
        if ($this->health < 0) {
            $this->health = 0;
        }
    }
}
