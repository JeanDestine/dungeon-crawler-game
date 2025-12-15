<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\IO\CliIO;
use App\Classes\Game;

$io = new CliIO();
$savePath = __DIR__ . '/../var/save.json';

$game = new Game($io, $savePath);
$game->run();
