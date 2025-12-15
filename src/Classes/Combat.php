<?php

namespace App\Classes;

use App\Classes\Character\Monster;
use App\Classes\Character\Player;

class Combat
{
    public function __construct(
        public readonly Player $attacker,
        public readonly Monster $defender,
    ) {}

    public function executeAttack(int $damage): void
    {
        $this->defender->takeDamage($damage);
    }

    public function isDefenderDead(): bool
    {
        return $this->defender->isDead();
    }

    public function fight(): array
    {
        $log = [];
        $round = 1;

        while (!$this->isDefenderDead() && !$this->attacker->isDead()) {
            $log[] = "Round {$round}: {$this->attacker->name} attacks {$this->defender->name} for {$this->attacker->weapon->damage} damage.";
            $this->executeAttack($this->attacker->weapon->damage);

            if ($this->isDefenderDead()) {
                $log[] = "{$this->defender->name} has been defeated!";
                break;
            }

            // Swap roles for next round
            [$this->attacker, $this->defender] = [$this->defender, $this->attacker];
            $round++;
        }

        return $log;
    }
}
