<?php

namespace App\Classes\Character;

use App\Classes\Character;
use App\Enums\Character\Type as CharacterType;

class Monster extends Character
{
    public function __construct(
        public string $name,
        public CharacterType $type = CharacterType::MONSTER,
        public int $health = 100,
        public int $damage = 10
    ) {
        parent::__construct($name, $type, $health);
    }

    public static function random(int $difficulty): self
    {
        $monsters = [
            new Monster('Goblin', CharacterType::MONSTER, ($difficulty * 25), (5 * $difficulty)),
            new Monster('Orc', CharacterType::MONSTER, ($difficulty * 50), (10 * $difficulty)),
            new Monster('Troll', CharacterType::MONSTER, ($difficulty * 100), (20 * $difficulty)),
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
        if (empty($data)) {
            return self::defaultMonster();
        }

        return new self(
            name: $data['name'] ?? 'Goblin',
            health: $data['health'] ?? 50,
            type: CharacterType::MONSTER,
            damage: $data['damage'] ?? 5,
        );
    }

    private static function defaultMonster(): self
    {
        return new self(
            name: 'Goblin',
            health: 50,
            damage: 5,
        );
    }
}
