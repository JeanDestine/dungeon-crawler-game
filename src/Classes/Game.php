<?php

declare(strict_types=1);

namespace App\Classes;

use App\Classes\Character\Player;
use App\Interfaces\IO;
use App\Enums\Room\Type as RoomType;
use App\Enums\Character\Type as CharacterType;

final class Game
{
    private Player $player;
    private Dungeon $dungeon;

    public function __construct(
        private IO $io,
        private string $savePath
    ) {}

    public function run(): void
    {
        $this->io->writeln("=== Text Dungeon (PHP) ===");
        $this->io->writeln("Type 'help' for commands.");
        $this->io->writeln();

        // Start new game by default, but offer load if save exists.
        if (is_file($this->savePath)) {
            $answer = $this->io->read("Saved game found. Type 'load' to continue or press Enter for new: ");
            if ($answer === 'load') {
                if ($this->load()) {
                    $this->io->writeln("Loaded saved game.");
                } else {
                    $this->io->writeln("Failed to load save. Starting new game.");
                    $this->newGame();
                }
            } else {
                $this->newGame();
            }
        } else {
            $this->newGame();
        }

        $this->enterRoom(); // describe + resolve current tile

        while (true) {
            if ($this->player->isDead()) {
                $this->io->writeln();
                $this->io->writeln("GAME OVER. Final score: {$this->player->score}");
                return;
            }

            $room = $this->dungeon->getRoomAtPosition($this->player->position);
            if ($room->type === RoomType::EXIT) {
                $this->io->writeln();
                $this->io->writeln("You found the EXIT!");
                $this->io->writeln("Final score: {$this->player->score}");
                return;
            }

            $cmd = $this->io->read("> ");
            if ($cmd === '') {
                continue;
            }

            if ($this->handleCommand($cmd)) {
                continue;
            }

            $this->io->writeln("Unknown command. Type 'help'.");
        }
    }

    private function newGame(): void
    {
        $this->player = new Player('test', CharacterType::PLAYER);
        $this->dungeon = Dungeon::generate(5, 5);

        $this->dungeon->markVisited($this->player->position);
        $this->io->writeln("New game started. Find the exit (E).");
        $this->io->writeln();
    }

    private function handleCommand(string $cmd): bool
    {
        // Movement aliases
        $cmd = match ($cmd) {
            'n' => 'north',
            's' => 'south',
            'e' => 'east',
            'w' => 'west',
            default => $cmd,
        };

        return match ($cmd) {
            'north', 'south', 'east', 'west' => $this->move($cmd),
            'look' => $this->look(),
            'stats' => $this->stats(),
            'map' => $this->map(),
            'help' => $this->help(),
            'save' => $this->save(),
            'load' => $this->load(),
            'quit', 'exit' => $this->quit(),
            default => false,
        };
    }

    private function move(string $direction): bool
    {
        $dx = 0;
        $dy = 0;
        if ($direction === 'north') $dy = -1;
        if ($direction === 'south') $dy = 1;
        if ($direction === 'west')  $dx = -1;
        if ($direction === 'east')  $dx = 1;

        $newPosition = $this->player->forecastedMove($dx, $dy);

        if (!$this->dungeon->inBounds($newPosition)) {
            $this->io->writeln("You can't go that way.");
            return true;
        }

        $this->player->move($newPosition);
        $this->dungeon->markVisited($newPosition);

        $this->enterRoom();
        return true;
    }

    private function enterRoom(): void
    {
        $room = $this->dungeon->getRoomAtPosition($this->player->position);

        $this->io->writeln();
        $this->io->writeln("You are in room ({$this->player->position->x},{$this->player->position->y}).");
        $this->io->writeln($room->describe());

        // Resolve encounter once (if treasure/monster still present)
        if ($room->type === RoomType::TREASURE && $room->treasure > 0) {
            $amount = $room->treasure;
            $this->player->addTreasure($amount);

            $this->io->writeln("You collect treasure worth {$amount}. Score: {$this->player->score}");

            // Treasure consumed -> room becomes empty
            $room->treasure = 0;
            $room->type = RoomType::EMPTY;
            $this->dungeon->setRoomByPosition($this->player->position, $room);
        }

        if ($room->type === RoomType::MONSTER && $room->monster !== null && !$room->monster->isDead()) {
            $combat = new Combat($this->player, $room->monster);
            $log = $combat->fight();
            foreach ($log as $line) {
                $this->io->writeln($line);
            }

            if ($room->monster->isDead()) {
                // Monster defeated -> room becomes empty
                $room->monster = null;
                $room->type = RoomType::EMPTY;
                $this->dungeon->setRoomByPosition($this->player->position, $room);

                // Small reward
                $bonus = random_int(3, 10);
                $this->player->addTreasure($bonus);
                $this->io->writeln("You find {$bonus} coins on the corpse. Score: {$this->player->score}");
            }
        }

        if ($room->type === RoomType::EXIT) {
            $this->io->writeln("One step more and you'll be out...");
        }

        $this->io->writeln();
    }

    private function look(): bool
    {
        $room = $this->dungeon->getRoomAtPosition($this->player->position);
        $this->io->writeln($room->describe());
        return true;
    }

    private function stats(): bool
    {
        $inv = $this->player->inventory;
        $invText = empty($inv) ? '(empty)' : implode(', ', $inv);

        $this->io->writeln("HP: {$this->player->health}");
        $this->io->writeln("Score: {$this->player->score}");
        $this->io->writeln("Inventory: {$invText}");
        return true;
    }

    private function map(): bool
    {
        $this->io->writeln($this->dungeon->renderVisitedMap($this->player->position->x, $this->player->position->y));
        $this->io->writeln("Legend: @=you, ?=unknown, .=empty, M=monster room, T=treasure room, E=exit");
        return true;
    }

    private function help(): bool
    {
        $this->io->writeln("Commands:");
        $this->io->writeln("  north/south/east/west (or n/s/e/w) ");
        $this->io->writeln("  look, stats, map");
        $this->io->writeln("  save, load");
        $this->io->writeln("  help, quit");
        return true;
    }

    private function save(): bool
    {
        $data = [
            'player' => $this->player->toArray(),
            'dungeon' => $this->dungeon->toArray(),
            'savedAt' => date(DATE_ATOM),
        ];

        $dir = dirname($this->savePath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        file_put_contents($this->savePath, json_encode($data, JSON_PRETTY_PRINT));
        $this->io->writeln("Saved to {$this->savePath}");
        return true;
    }

    private function load(): bool
    {
        if (!is_file($this->savePath)) {
            $this->io->writeln("No save file found.");
            return true;
        }

        $json = file_get_contents($this->savePath);
        if ($json === false) {
            $this->io->writeln("Could not read save file.");
            return true;
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            $this->io->writeln("Save file is corrupted.");
            return true;
        }

        $this->player = Player::fromArray($data['player'] ?? []);
        $this->dungeon = Dungeon::fromArray($data['dungeon'] ?? []);

        // Ensure current room is marked visited (just in case)
        $this->dungeon->markVisited($this->player->position);

        $this->io->writeln("Game loaded.");
        $this->enterRoom();
        return true;
    }

    private function quit(): bool
    {
        $this->io->writeln("Bye!");
        exit(0);
    }
}
