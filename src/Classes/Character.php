<?php

namespace App\Classes;

use App\Enums\Character\Type;
use InvalidArgumentException;

class Character
{
    public function __construct(
        public string $name,
        public int $health = 100,
        public Type $type
    ) {
        if($health <= 0){
            throw new InvalidArgumentException("Health must be greater than zero.");
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
