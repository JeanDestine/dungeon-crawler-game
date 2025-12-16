<?php

namespace Tests\Unit;

use App\Classes\Character\Monster;
use App\Classes\Room;
use App\Enums\Room\Type;
use PHPUnit\Framework\TestCase;

class RoomTest extends TestCase
{
    public function testRoomsCanBeCreated(): void
    {
        $monsterRoom = new Room(Type::MONSTER, new Monster('Goblin'));
        $treasureRoom = new Room(Type::TREASURE, null, 100);
        $emptyRoom = new Room(Type::EMPTY);
        $exitRoom = new Room(Type::EXIT);

        foreach ([$monsterRoom, $treasureRoom, $emptyRoom, $exitRoom] as $room) {
            $this->assertInstanceOf(Room::class, $room);
        }
    }

    public function testRoomsShouldBeValidated(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Room(Type::MONSTER);

        $this->expectException(\InvalidArgumentException::class);
        new Room(Type::TREASURE);
    }

    public function testRoomDescriptions(): void
    {
        $monsterRoom = new Room(Type::MONSTER, new Monster('Dragon'));
        $treasureRoom = new Room(Type::TREASURE, null, 500);
        $emptyRoom = new Room(Type::EMPTY);
        $exitRoom = new Room(Type::EXIT);

        $this->assertEquals("A wild Dragon appears!", $monsterRoom->describe());
        $this->assertEquals("You see a treasure chest containing 500 gold coins!", $treasureRoom->describe());
        $this->assertEquals("The room is empty.", $emptyRoom->describe());
        $this->assertEquals("This room contains the exit. Freedom is near!", $exitRoom->describe());
    }

    public function testRoomToArrayAndFromArray(): void
    {
        $monsterRoom = new Room(Type::MONSTER, new Monster('Orc'));
        $treasureRoom = new Room(Type::TREASURE, null, 200);
        $emptyRoom = new Room(Type::EMPTY);
        $exitRoom = new Room(Type::EXIT);

        $rooms = [$monsterRoom, $treasureRoom, $emptyRoom, $exitRoom];

        foreach ($rooms as $room) {
            $roomArray = $room->toArray();
            $restoredRoom = Room::fromArray($roomArray);

            $this->assertEquals($room->type, $restoredRoom->type);
            $this->assertEquals($room->treasure, $restoredRoom->treasure);
            $this->assertEquals($room->visited, $restoredRoom->visited);

            if ($room->monster) {
                $this->assertEquals($room->monster->name, $restoredRoom->monster->name);
            } else {
                $this->assertNull($restoredRoom->monster);
            }
        }
    }
}
