<?php

namespace App\Classes\Character;

use App\Classes\Character;
use App\Enums\Character\Type as CharacterType;

class Monster extends Character
{
    public function __construct(
        public string $name,
        public int $health = 100,
        public CharacterType $type,
        public int $damage = 10
    ) {
        parent::__construct($name, $health, $type);
    }

    public static function random(): self
    {
        $monsters = [
            new Monster('Goblin', 50, CharacterType::MONSTER, 5),
            new Monster('Orc', 80, CharacterType::MONSTER, 15),
            new Monster('Troll', 120, CharacterType::MONSTER, 20),
        ];

        return $monsters[array_rand($monsters)];
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'health' => $this->health,
            'type' => $this->type->value,
            'damage' => $this->damage,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            health: $data['health'],
            type: CharacterType::from($data['type']),
            damage: $data['damage'],
        );
    }
}
