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

    public function executeAttack(int $damage, Character $target): void
    {
        $target->takeDamage($damage);
    }

    public function fight(): array
    {
        $log = [];
        $round = 1;
        $attacker = $this->attacker;
        $defender = $this->defender;

        while (!$this->defender->isDead() && !$this->attacker->isDead()) {
            $damage = $attacker instanceof Player ? $attacker->weapon->damage : $attacker->damage;

            $this->executeAttack($damage, $defender);
            $player = $attacker instanceof Player ? $attacker : $defender;
            $monster = $attacker instanceof Monster ? $attacker : $defender;

            $damageInfo = "\t\t\t {$player->name} has {$player->health} HP remaining. \t\t\t {$monster->name} has {$monster->health} HP remaining.";
            $log[] = "Round {$round}: {$attacker->name} attacks {$defender->name} for {$damage} damage. " . $damageInfo;

            if ($defender->isDead()) {
                $log[] = "{$defender->name} has been defeated!";
                break;
            }

            // Swap roles
            [$attacker, $defender] = $this->determineNewRole($attacker, $defender);
            $round++;
        }

        return $log;
    }

    private function determineNewRole(Character $currentAttacker, Character $currentDefender): array
    {
        $randomValue = random_int(1, 100);
        return  $randomValue % 2 === 0 ? [$currentAttacker, $currentDefender] : [$currentDefender, $currentAttacker];
    }
}
