<?php

namespace App\Classes;

use App\Enums\Character\Type;

class Character
{
    public function __construct(
        public string $name,
        public int $health = 100,
        public Type $type
    ) {}

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
