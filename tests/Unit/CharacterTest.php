<?php

namespace Tests\Unit;

use App\Classes\Character;
use App\Enums\Character\Type;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Throwable;

class CharacterTest extends TestCase
{
    public function testPlayerCharacterCanBeCreated(): void
    {
        $character = new Character('PlayerCharacter', Type::PLAYER, 100);

        $this->assertInstanceOf(Character::class, $character);
        $this->assertEquals('PlayerCharacter', $character->name);
        $this->assertEquals(100, $character->health);
        $this->assertEquals(Type::PLAYER, $character->type);
    }

    public function testMonsterCharacterCanBeCreated(): void
    {
        $character = new Character('MonsterCharacter', Type::MONSTER, 100);

        $this->assertInstanceOf(Character::class, $character);
        $this->assertEquals('MonsterCharacter', $character->name);
        $this->assertEquals(100, $character->health);
        $this->assertEquals(Type::MONSTER, $character->type);
    }

    public function testExceptionThrownForNullCharacterType(): void
    {
        $this->expectException(Throwable::class);
        new Character('InvalidCharacter', null, 100);
    }

    public function testExceptionThrownForInvalidCharacterType(): void
    {
        $this->expectException(Throwable::class);
        new Character('InvalidCharacter', 'INVALID_TYPE', 100);
    }

    public function testCharacterCanTakeDamage(): void
    {
        $initialHealth = 100;
        $damage = 30;
        $character = new Character('TestCharacter', Type::PLAYER, $initialHealth);
        $character->takeDamage($damage);

        $this->assertEquals(($initialHealth - $damage), $character->health);
    }

    public function testCharacterHealthDoesNotGoBelowZero(): void
    {
        $initialHealth = 50;
        $damage = 100;
        $character = new Character('TestCharacter', Type::PLAYER, $initialHealth);
        $character->takeDamage($damage);

        $this->assertEquals(0, $character->health);
        $this->assertTrue($character->isDead());
    }

    public function testCharacterCannotBeCreatedWithZeroHealth(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Character('TestCharacter', Type::PLAYER, 0);
    }

    public function testCharacterIsNotDeadWhenHealthIsAboveZero(): void
    {
        $character = new Character('TestCharacter', Type::PLAYER, 50);

        $this->assertFalse($character->isDead());
    }
}
