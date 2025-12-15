<?php

namespace App\Enums\Room;

enum Type: string
{
    case EMPTY = 'empty';
    case MONSTER = 'monster';
    case TREASURE = 'treasure';
    case EXIT = 'exit';
}
