<?php

namespace Tests\Unit;

use App\Classes\{Dungeon, Position, Room};
use App\Classes\Character\Monster;
use App\Enums\Room\Type as RoomType;
use PHPUnit\Framework\TestCase;

class DungeonTest extends TestCase
{
    public function testDungeonCanBeCreated(): void
    {
        $dungeon = Dungeon::generate(5, 5, rand(1, 3));
        $this->assertInstanceOf(Dungeon::class, $dungeon);
        $this->assertGreaterThan(0, count($dungeon->rooms));

        foreach ($dungeon->rooms as $room) {
            $this->assertInstanceOf(Room::class, $room);
        }

        $this->assertDungeonProperties($dungeon, [
            'width' => 5,
            'height' => 5,
            'entrance_x' => 0,
            'entrance_y' => 0,
        ]);

        $createdDungeon = $this->createSampleDungeon();

        $this->assertDungeonProperties($createdDungeon, [
            'width' => 4,
            'height' => 4,
            'entrance_x' => 0,
            'entrance_y' => 0,
        ]);
    }

    public function testDungeonToArrayAndFromArray(): void
    {
        $dungeon = Dungeon::generate(4, 4, rand(1, 3));
        $dungeonArray = $dungeon->toArray();
        $restoredDungeon = Dungeon::fromArray($dungeonArray);

        $this->assertEquals($dungeon->width, $restoredDungeon->width);
        $this->assertEquals($dungeon->height, $restoredDungeon->height);
        $this->assertEquals(count($dungeon->rooms), count($restoredDungeon->rooms));
    }

    public function testDungeonStartAndExitPositions(): void
    {
        $dungeon = Dungeon::generate(6, 6, rand(1, 3));

        $this->assertEquals(0, $dungeon->entrance->x);
        $this->assertEquals(0, $dungeon->entrance->y);
        $this->assertEquals(5, $dungeon->exit->x);
        $this->assertEquals(5, $dungeon->exit->y);
    }

    public function testDungeonInvalidDimensions(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Dungeon::generate(0, 5, rand(1, 3));

        $this->expectException(\InvalidArgumentException::class);
        Dungeon::generate(5, -3, rand(1, 3));

        $this->expectException(\InvalidArgumentException::class);
        Dungeon::generate(-rand(1, 10), -rand(1, 10), rand(1, 3));
    }

    public function testDungeonRoomRetrieval(): void
    {
        $dungeon = Dungeon::generate(3, 3, rand(1, 3));
        $position = $dungeon->entrance;
        $room = $dungeon->getRoomAtPosition($position);

        $this->assertNotNull($room);
        $this->assertEquals(0, $position->x);
        $this->assertEquals(0, $position->y);
    }

    public function testDungeonRoomSetting(): void
    {
        $dungeon = Dungeon::generate(3, 3, rand(1, 3));
        $newRoom = $dungeon->getRoomAtPosition($dungeon->entrance);
        $position = $dungeon->entrance;

        $dungeon->setRoomByPosition($position, $newRoom);
        $retrievedRoom = $dungeon->getRoomAtPosition($position);

        $this->assertSame($newRoom, $retrievedRoom);
    }

    public function testDungeonMarkRoomVisited(): void
    {
        $dungeon = Dungeon::generate(3, 3, rand(1, 3));
        $position = $dungeon->entrance;

        $dungeon->markRoomVisited($position);
        $room = $dungeon->getRoomAtPosition($position);

        $this->assertTrue($room->visited);
    }

    public function testDungeonRenderVisitedMap(): void
    {
        $dungeon = $this->createSampleDungeon();
        $positionsToVisit = [
            new Position(0, 0),
            new Position(1, 0),
            new Position(1, 1),
            new Position(2, 2),
            new Position(3, 3),
        ];

        foreach ($positionsToVisit as $pos) {
            $dungeon->markRoomVisited($pos);
        }

        $map = $dungeon->renderVisitedMap(1, 1);
        $expectedMap = <<<MAP
E M ? ?
? @ ? ?
? ? E ?
? ? ? X
MAP;

        $this->assertEquals($expectedMap, $map);
    }

    public function testRoomPositionBounds(): void
    {
        $dungeon = $this->createSampleDungeon();
        $outOfBoundsPosition = new Position(5, 5);
        $this->assertFalse($dungeon->isPositionWithinBounds($outOfBoundsPosition));
        $this->assertNull($dungeon->getRoomAtPosition($outOfBoundsPosition));
    }

    private function createSampleDungeon(): Dungeon
    {
        $width  = 4;
        $height = 4;

        $startPosition = new Position(0, 0);
        $exitPosition  = new Position(3, 3);

        $dungeon = new Dungeon($width, $height, $startPosition, $exitPosition);

        // Define dungeon layout (y, x)
        $layout = [
            // y = 0
            ['S', 'M', 'T', 'T'],
            // y = 1
            ['M', 'M', 'E', 'M'],
            // y = 2
            ['T', 'T', 'E', 'E'],
            // y = 3
            ['T', 'T', 'E', 'X'],
        ];

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $cell = $layout[$y][$x];
                $position = new Position($x, $y);

                match ($cell) {
                    'S' => $dungeon->setRoomByPosition(
                        $position,
                        new Room(RoomType::EMPTY)
                    ),

                    'M' => $dungeon->setRoomByPosition(
                        $position,
                        new Room(RoomType::MONSTER, monster: Monster::random(rand(1, 3)))
                    ),

                    'T' => $dungeon->setRoomByPosition(
                        $position,
                        new Room(RoomType::TREASURE, treasure: random_int(5, 25))
                    ),

                    'E' => $dungeon->setRoomByPosition(
                        $position,
                        new Room(RoomType::EMPTY)
                    ),

                    'X' => $dungeon->setRoomByPosition(
                        $position,
                        new Room(RoomType::EXIT)
                    ),
                };
            }
        }

        return $dungeon;
    }

    private function assertDungeonProperties(Dungeon $dungeon, array $positions): void
    {
        $this->assertEquals($positions['width'] ?? 0, $dungeon->width);
        $this->assertEquals($positions['height'] ?? 0, $dungeon->height);
        $this->assertEquals($positions['entrance_x'] ?? 0, $dungeon->entrance->x);
        $this->assertEquals($positions['entrance_y'] ?? 0, $dungeon->entrance->y);
    }
}
