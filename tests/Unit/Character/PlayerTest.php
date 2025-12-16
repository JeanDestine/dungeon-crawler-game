<?php

namespace Tests\Unit;

use App\Classes\Character\Player;
use App\Classes\Position;
use App\Enums\Character\Type;
use PHPUnit\Framework\TestCase;

class PlayerTest extends TestCase
{
    public function testPlayerMovement(): void
    {
        $deltaX = 5;
        $deltaY = -3;
        $player = new Player('Hero', 100);
        $initialPosition = $player->position;

        $delta = new Position($deltaX, $deltaY);
        $forcastedPosition = $player->forecastedMove($deltaX, $deltaY);
        $newPosition = $player->move($delta);

        $this->assertEquals($initialPosition->x + $deltaX, $newPosition->x);
        $this->assertEquals($initialPosition->y + $deltaY, $newPosition->y);
        $this->assertEquals($newPosition, $forcastedPosition);
    }

    public function testPlayerScoreIncrement(): void
    {
        $player = new Player('Hero', 100);
        $initialScore = $player->score;

        $player->addTreasure(50);

        $this->assertEquals($initialScore + 50, $player->score);
    }

    public function testPlayerToArrayAndFromArray(): void
    {
        $player = new Player('Hero', 100, Type::PLAYER);
        $player->addTreasure(100);
        $player->move(new Position(2, 3));

        $playerArray = $player->toArray();
        $restoredPlayer = Player::fromArray($playerArray);

        $this->assertEquals($player->name, $restoredPlayer->name);
        $this->assertEquals($player->health, $restoredPlayer->health);
        $this->assertEquals($player->score, $restoredPlayer->score);
        $this->assertEquals($player->position->x, $restoredPlayer->position->x);
        $this->assertEquals($player->position->y, $restoredPlayer->position->y);
        $this->assertEquals($player->isDead(), $restoredPlayer->isDead());
    }
}
