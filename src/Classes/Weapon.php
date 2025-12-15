<?php

namespace App\Classes;

use App\Enums\Weapon\Type;

class Weapon
{
    public function __construct(
        public readonly string $name,
        public readonly int $damage,
        public readonly Type $type
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'damage' => $this->damage,
            'type' => $this->type->value,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            damage: $data['damage'],
            type: Type::from($data['type']),
        );
    }
}
