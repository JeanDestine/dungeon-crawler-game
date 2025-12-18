<?php

namespace Tests\Unit;

use App\Classes\Character\{Monster, Player};
use App\Classes\Combat;
use PHPUnit\Framework\TestCase;

class CombatTest extends TestCase
{
    protected Monster $monster;
    protected Player $player;
    protected Combat $combat;

    public function setUp(): void
    {
        $this->monster = new Monster('Goblin', health: 50);
        $this->player = new Player('Test');
        $this->combat = new Combat($this->player, $this->monster);
    }
    public function testExecuteDamageToMonster(): void
    {
        $damage = 25;
        $this->combat->executeAttack($damage, $this->monster);
        $this->assertEquals((50 - $damage), $this->monster->health);
        $this->assertFalse($this->combat->defender->isDead());

        $this->combat->executeAttack($damage, $this->monster);
        $this->assertEquals((50 - 2 * $damage), $this->monster->health);
        $this->assertTrue($this->combat->defender->isDead());
    }

    public function testFightUntilMonsterDefeated(): void
    {
        $log = $this->combat->fight();

        $this->assertNotEmpty($log);
        $this->assertTrue($this->combat->defender->isDead());
        $this->assertStringContainsString('has been defeated', end($log));
    }
}
