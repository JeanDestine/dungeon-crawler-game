<?php

namespace App\Classes;

use App\Classes\Character\Monster;
use App\Enums\Room\Type;

class Room
{
    public function __construct(
        public Type $type,
        public ?Monster $monster = null,
        public ?int $treasure = null,
        public bool $visited = false
    ) {
        if ($this->type === Type::MONSTER && $this->monster === null) {
            throw new \InvalidArgumentException("Monster room must have a monster.");
        }

        if ($this->type === Type::TREASURE && $this->treasure === null) {
            throw new \InvalidArgumentException("Treasure room must have a treasure amount.");
        }
    }

    public function describe(): string
    {
        return match ($this->type) {
            Type::EMPTY => "The room is empty.",
            Type::TREASURE => "You see a treasure chest containing {$this->treasure} gold coins!",
            Type::MONSTER => "A wild {$this->monster->name} appears!",
            Type::EXIT => "This room contains the exit. Freedom is near!",
        };
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'monster' => $this->monster ? $this->monster->toArray() : null,
            'treasure' => $this->treasure,
            'visited' => $this->visited,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            type: Type::from($data['type']),
            monster: isset($data['monster']) ? Monster::fromArray($data['monster']) : null,
            treasure: $data['treasure'] ?? null,
            visited: $data['visited'] ?? false
        );
    }
}
