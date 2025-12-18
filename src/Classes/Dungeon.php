<?php

namespace App\Classes;

use App\Classes\Character\Monster;
use App\Enums\Room\Type as RoomType;

class Dungeon
{
    public function __construct(
        public readonly int $width,
        public readonly int $height,
        public readonly Position $entrance,
        public readonly Position $exit,
        public array $rooms = [],
    ) {
        if ($width <= 0 || $height <= 0) {
            throw new \InvalidArgumentException('Dungeon dimensions must be positive integers.');
        }
    }

    public static function generate(int $width, int $height, int $difficulty): self
    {
        $startPosition = new Position(0, 0);
        $exitPosition = new Position($width - 1, $height - 1);
        $dungeon = new self($width, $height, $startPosition, $exitPosition);

        // Fill rooms with content (simple, deterministic constraints: start is empty, exit is exit)
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if ($x === $startPosition->x && $y === $startPosition->y) {
                    $dungeon->setRoomByPosition($startPosition, new Room(RoomType::EMPTY));
                    continue;
                }
                if ($x === $exitPosition->x && $y === $exitPosition->y) {
                    $dungeon->setRoomByPosition($exitPosition, new Room(RoomType::EXIT));
                    continue;
                }

                // Weighted random: monster 30%, treasure 25%, empty 45%
                $r = random_int(1, 100);
                if ($r <= 30) {
                    $dungeon->setRoomByPosition(new Position($x, $y), new Room(RoomType::MONSTER, monster: Monster::random($difficulty)));
                } elseif ($r <= 55) {
                    $dungeon->setRoomByPosition(new Position($x, $y), new Room(RoomType::TREASURE, treasure: random_int(5, 25)));
                } else {
                    $dungeon->setRoomByPosition(new Position($x, $y), new Room(RoomType::EMPTY));
                }
            }
        }

        return $dungeon;
    }

    public function getRoomAtPosition(Position $position): ?Room
    {
        $key = $this->key($position);
        if (!isset($this->rooms[$key])) {
            return null;
        }
        return $this->rooms[$key];
    }

    public function isPositionWithinBounds(Position $position): bool
    {
        return $position->x >= 0 && $position->x < $this->width &&
            $position->y >= 0 && $position->y < $this->height;
    }

    public function setRoomByPosition(Position $position, Room $room): void
    {
        $this->rooms[$this->key($position)] = $room;
    }

    public function markRoomVisited(Position $position): void
    {
        $room = $this->getRoomAtPosition($position);

        if ($room === null) {
            throw new \RuntimeException("No room found at position ({$position->x}, {$position->y}) to mark as visited.");
        }

        $room->visited = true;
        $this->setRoomByPosition($position, $room);
    }

    public function key(Position $position): string
    {
        return $position->positionKey();
    }

    public function renderVisitedMap(int $playerX, int $playerY): string
    {
        $out = [];
        for ($y = 0; $y < $this->height; $y++) {
            $row = '';
            for ($x = 0; $x < $this->width; $x++) {
                if ($x === $playerX && $y === $playerY) {
                    $row .= '@ ';
                    continue;
                }

                $room = $this->getRoomAtPosition(new Position($x, $y));
                if (!$room->visited) {
                    $row .= '? ';
                    continue;
                }

                $row .= match ($room->type) {
                    RoomType::EXIT => 'X ',
                    RoomType::MONSTER => 'M ',
                    RoomType::TREASURE => 'T ',
                    RoomType::EMPTY => 'E ',
                    default => '. ',
                };
            }
            $out[] = rtrim($row);
        }
        return implode(PHP_EOL, $out);
    }

    public function toArray(): array
    {
        $roomsArray = [];
        foreach ($this->rooms as $key => $room) {
            $roomsArray[$key] = $room->toArray();
        }

        return [
            'width' => $this->width,
            'height' => $this->height,
            'rooms' => $roomsArray,
            'entrance' => $this->entrance->toArray(),
            'exit' => $this->exit->toArray(),
        ];
    }

    public static function fromArray(array $data): self
    {
        $rooms = [];
        foreach ($data['rooms'] as $key => $roomData) {
            $rooms[$key] = Room::fromArray($roomData);
        }

        return new self(
            width: $data['width'],
            height: $data['height'],
            rooms: $rooms,
            entrance: Position::fromArray($data['entrance']),
            exit: Position::fromArray($data['exit']),
        );
    }
}
