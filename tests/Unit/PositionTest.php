<?php

namespace Tests\Unit;

use App\Classes\Position;
use PHPUnit\Framework\TestCase;

class PositionTest extends TestCase
{
    public function testPositionCanBeCreated(): void
    {
        $x = 5;
        $y = 10;
        $position = new Position($x, $y);

        $this->assertInstanceOf(Position::class, $position);
        $this->assertEquals($x, $position->x);
        $this->assertEquals($y, $position->y);
    }

    public function testPositionKey(): void
    {
        $position = new Position(3, 7);
        $this->assertEquals('3,7', $position->positionKey());
    }

    public function testToArrayAndFromArray(): void
    {
        $position = new Position(8, 12);
        $positionArray = $position->toArray();
        $this->assertEquals(['x' => 8, 'y' => 12], $positionArray);

        $newPosition = Position::fromArray($positionArray);
        $this->assertEquals($position->x, $newPosition->x);
        $this->assertEquals($position->y, $newPosition->y);
    }
}
