<?php

namespace App\Classes\Character;

use App\Classes\Character;
use App\Classes\Position;
use App\Classes\Weapon;
use App\Classes\Weapon\Fist;
use App\Enums\Character\Type as CharacterType;

class Player extends Character
{
    public function __construct(
        public string $name,
        public CharacterType $type,
        public int $health = 100,
        public int $score = 0,
        public Weapon $weapon = new Fist(),
        public mixed $inventory = [],
        public Position $position = new Position(0, 0)
    ) {
        parent::__construct($name, $health, $type);
        $this->inventory[] = $this->weapon;
    }

    public function move(Position $delta): Position
    {
        $this->position->x += $delta->x;
        $this->position->y += $delta->y;

        return $this->position;
    }

    public function forecastedMove(int $x, int $y): Position
    {
        return new Position(
            $this->position->x + $x,
            $this->position->y + $y
        );
    }

    public function addTreasure(int $amount): void
    {
        $this->score += $amount;
    }

    public function toArray(): array
    {
        return [
            'hp' => $this->health,
            'score' => $this->score,
            'x' => $this->position->x,
            'y' => $this->position->y,
            'inventory' => $this->inventory,
            'name' => $this->name,
            'weapon' => $this->weapon->toArray(),
        ];
    }

    public static function fromArray(array $data): self
    {
        $p = new self(
            health: (int)($data['hp'] ?? 25),
            score: (int)($data['score'] ?? 0),
            position: new Position(
                x: (int)($data['x'] ?? 0),
                y: (int)($data['y'] ?? 0),
            ),
            name: (string)($data['name'] ?? 'Hero'),
            weapon: Weapon::fromArray($data['weapon'] ?? []),
            type: CharacterType::PLAYER,
        );

        $p->inventory = array_values(array_map('strval', $data['inventory'] ?? []));
        return $p;
    }
}
