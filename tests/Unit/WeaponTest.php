<?php

namespace Tests\Unit;

use App\Classes\Weapon;
use App\Classes\Weapon\{Bat, Sword, Fist};
use App\Enums\Weapon\Type;
use PHPUnit\Framework\TestCase;

class WeaponTest extends TestCase
{
    public function testSwordCanBeCreated(): void
    {
        $weapon = new Sword();

        $this->assertInstanceOf(Sword::class, $weapon);
        $this->assertEquals(50, $weapon->damage);
        $this->assertEquals(Type::SWORD, $weapon->type);
    }

    public function testBatCanBeCreated(): void
    {
        $weapon = new Bat();

        $this->assertInstanceOf(Bat::class, $weapon);
        $this->assertEquals(25, $weapon->damage);
        $this->assertEquals(Type::BAT, $weapon->type);
    }

    public function testFistCanBeCreated(): void
    {
        $weapon = new Fist();

        $this->assertInstanceOf(Fist::class, $weapon);
        $this->assertEquals(10, $weapon->damage);
        $this->assertEquals(Type::FISTS, $weapon->type);
    }

    public function testWeaponToArrayAndFromArray(): void
    {
        $sword = new Sword();
        $swordArray = $sword->toArray();
        $swordWeapon = Weapon::fromArray($swordArray);

        $fist = new Fist();
        $fistArray = $fist->toArray();
        $fistWeapon = Fist::fromArray($fistArray);

        $bat = new Bat();
        $batArray = $bat->toArray();
        $batWeapon = Bat::fromArray($batArray);

        $this->assertEquals($sword->name, $swordWeapon->name);
        $this->assertEquals($sword->damage, $swordWeapon->damage);
        $this->assertEquals($sword->type, $swordWeapon->type);

        $this->assertEquals($fist->name, $fistWeapon->name);
        $this->assertEquals($fist->damage, $fistWeapon->damage);
        $this->assertEquals($fist->type, $fistWeapon->type);

        $this->assertEquals($bat->name, $batWeapon->name);
        $this->assertEquals($bat->damage, $batWeapon->damage);
        $this->assertEquals($bat->type, $batWeapon->type);
    }
}
