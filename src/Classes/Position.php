<?php

namespace App\Classes;

class Position
{
    public function __construct(
        public int $x,
        public int $y
    ) {}

    public function positionKey(): string
    {
        return "{$this->x},{$this->y}";
    }

    public function toArray(): array
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            x: $data['x'],
            y: $data['y'],
        );
    }
}
