<?php

namespace Tests\Unit;

use App\Classes\Character\Monster;
use App\Enums\Character\Type;
use PHPUnit\Framework\TestCase;

class MonsterTest extends TestCase
{
    public function testMonsterCharacterCanBeCreated(): void
    {
        $monster = new Monster('Monster', 150);

        $this->assertInstanceOf(Monster::class, $monster);
        $this->assertEquals('Monster', $monster->name);
        $this->assertEquals(150, $monster->health);
        $this->assertEquals(Type::MONSTER, $monster->type);
    }

    public function testMonsterToArrayAndFromArray(): void
    {
        $monster = new Monster('Monster', 150, Type::MONSTER);
        $monster->takeDamage(30);

        $monsterArray = $monster->toArray();
        $restoredMonster = Monster::fromArray($monsterArray);
        $this->assertEquals($monster->name, $restoredMonster->name);
        $this->assertEquals($monster->health, $restoredMonster->health);
        $this->assertEquals($monster->type, $restoredMonster->type);
        $this->assertEquals($monster->damage, $restoredMonster->damage);
        $this->assertEquals($monster->isDead(), $restoredMonster->isDead());
    }
}
