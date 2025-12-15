<?php

namespace App\Classes;

use App\Classes\Character\Monster;
use App\Enums\Room\Type as RoomType;

class Dungeon
{
    public function __construct(
        public readonly int $width,
        public readonly int $height,
        public array $rooms = [],
        public readonly Position $entrance,
        public readonly Position $exit,
    ) {}

    public static function generate(int $width, int $height): self
    {
        // TODO: implement
        // Start at (0,0) in logical space, but store as grid coordinates.
        $startPosition = new Position(0, 0);
        $exitPosition = new Position($width - 1, $height - 1);

        $exitX = $width - 1;
        $exitY = $height - 1;

        $d = new self($width, $height, [], $startPosition, $exitPosition);

        // Fill rooms with content (simple, deterministic constraints: start is empty, exit is exit)
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if ($x === $startPosition->x && $y === $startPosition->y) {
                    $d->setRoomByPosition($startPosition, new Room(RoomType::EMPTY));
                    continue;
                }
                if ($x === $exitPosition->x && $y === $exitPosition->y) {
                    $d->setRoomByPosition($exitPosition, new Room(RoomType::EXIT));
                    continue;
                }

                // Weighted random: monster 30%, treasure 25%, empty 45%
                $r = random_int(1, 100);
                if ($r <= 30) {
                    $d->setRoomByPosition(new Position($x, $y), new Room(RoomType::MONSTER, monster: Monster::random()));
                } elseif ($r <= 55) {
                    $d->setRoomByPosition(new Position($x, $y), new Room(RoomType::TREASURE, treasure: random_int(5, 25)));
                } else {
                    $d->setRoomByPosition(new Position($x, $y), new Room(RoomType::EMPTY));
                }
            }
        }

        return $d;
    }

    public function getRoomAtPosition(Position $position): ?Room
    {
        //TODO: Refractor to use a more efficient lookup if necessary
        $key = $this->key($position);
        if (!isset($this->rooms[$key])) {
            // Should not happen if generated correctly, but keep it safe.
            $this->rooms[$key] = new Room(RoomType::EMPTY);
        }
        return $this->rooms[$key];
    }

    public function inBounds(Position $position): bool
    {
        return $position->x >= 0 && $position->x < $this->width &&
            $position->y >= 0 && $position->y < $this->height;
    }

    public function setRoomByPosition(Position $position, Room $room): void
    {
        $this->rooms[$this->key($position)] = $room;
    }

    public function markVisited(Position $position): void
    {
        $room = $this->getRoomAtPosition($position);
        if ($room !== null) {
            // Throw exception
            return;
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
                    RoomType::EXIT => 'E ',
                    RoomType::MONSTER => 'M ',
                    RoomType::TREASURE => 'T ',
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
