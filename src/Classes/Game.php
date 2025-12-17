<?php

namespace App\Classes;

use App\Classes\Character\Player;
use App\Interfaces\IO;
use App\Enums\Room\Type as RoomType;

class Game
{
    const GAME_DIFFICULTY = [
        '1' => 5,
        '2' => 7,
        '3' => 10,
    ];

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
            try {
                if ($this->player->isDead()) {
                    $this->io->writeln();
                    $this->io->writeln("GAME OVER. Final score: {$this->player->score}");
                    return;
                }

                $room = $this->dungeon->getRoomAtPosition($this->player->position);

                if ($room === null) {
                    throw new \RuntimeException("Current room not found in dungeon data.");
                }

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
            } catch (\Throwable $th) {
                $this->io->writeln("An error occurred: " . $th->getMessage());
                throw $th;
            }
        }
    }

    private function gameSetup(): array
    {
        $this->io->writeln("Please input your character name:");
        $name = trim($this->io->read("Name: "));

        // TODO: validate name
        if (empty($name)) {
            $this->io->writeln("Invalid name. Please try again.");
        }

        $this->io->writeln("Welcome, {$name}!");
        $this->io->writeln('Please select difficulty: (1) Easy, (2) Medium, (3) Hard');
        $difficulty = trim($this->io->read("Difficulty (1-3): "));

        if (!in_array($difficulty, ['1', '2', '3'], true)) {
            $this->io->writeln("Invalid difficulty. Defaulting to Medium.");
            $difficulty = '2';
        }

        return [$difficulty, new Player($name)];
    }

    private function newGame(): void
    {
        [$difficulty, $player] = $this->gameSetup();
        $dungeonSize = self::GAME_DIFFICULTY[$difficulty] ?? 7;
        $this->player = $player;
        $this->dungeon = Dungeon::generate($dungeonSize, $dungeonSize);

        $this->dungeon->markRoomVisited($this->player->position);
        $this->io->writeln("New game started. Find the exit (E).");
        $this->io->writeln();
    }

    private function handleCommand(string $cmd): bool
    {
        $cmd = strtolower($cmd);
        // Movement aliases
        $cmd = match ($cmd) {
            // WASD controls
            'w' => 'up',
            'a' => 'left',
            's' => 'down',
            'd' => 'right',
            default => $cmd,
        };

        return match ($cmd) {
            'up', 'down', 'left', 'right' => $this->move($cmd),
            'look' => $this->look(),
            'stats' => $this->stats(),
            'map' => $this->map(),
            'help' => $this->help(),
            'save' => $this->save(),
            'load' => $this->load(),
            'quit', 'exit', 'q' => $this->quit(),
            default => false,
        };
    }

    private function move(string $direction): bool
    {
        $dx = 0;
        $dy = 0;

        [$dx, $dy] = match ($direction) {
            'up' => [0, 1],
            'down' => [0, -1],
            'left'  => [-1, 0],
            'right'  => [1, 0],
            default => [0, 0],
        };

        $newPosition = $this->player->forecastedMove($dx, $dy);

        if (!$this->dungeon->isPositionWithinBounds($newPosition)) {
            $this->io->writeln("You can't go that way.");
            return true;
        }

        $this->player->move($newPosition);
        $this->dungeon->markRoomVisited($newPosition);

        $this->enterRoom();
        return true;
    }

    private function enterRoom(): void
    {
        $room = $this->dungeon->getRoomAtPosition($this->player->position);

        $this->io->writeln();
        $this->io->writeln("You are in room ({$this->player->position->x},{$this->player->position->y}).");
        $this->io->writeln($room->describe());

        if ($room->type === RoomType::EXIT) {
            $this->io->writeln("One step more and you'll be out...");
        }

        if ($room->type === RoomType::TREASURE && $room->treasure > 0) {
            $this->processTreasureRoom($room);
        }

        if ($room->type === RoomType::MONSTER && $room->monster !== null && !$room->monster->isDead()) {
            $this->processMonsterRoom($room);
        }

        $this->io->writeln();
    }

    private function processTreasureRoom(Room $room): void
    {
        $amount = $room->treasure;

        $this->player->addTreasure($amount);
        $this->dungeon->setRoomByPosition($this->player->position, $room);
        $this->io->writeln("You collect treasure worth {$amount}. Score: {$this->player->score}");

        $room->treasure = 0;
    }

    private function processMonsterRoom(Room $room): void
    {
        $monsterHealth = $room->monster->health;
        $combat = new Combat($this->player, $room->monster);
        $log = $combat->fight();
        foreach ($log as $line) {
            $this->io->writeln($line);
            sleep(1); // pause for dramatic effect and readability
        }

        if ($room->monster->isDead()) {
            $room->monster = null;
            $this->dungeon->setRoomByPosition($this->player->position, $room);

            $healAmount = random_int(10, (int)($monsterHealth / 3));
            $this->player->heal($healAmount);
            $this->io->writeln("You feel reinvigorated and recovered, new health: {$this->player->health} HP (+{$healAmount})");

            // Small reward
            $bonus = random_int(3, 10);
            $this->player->addTreasure($bonus);
            $this->io->writeln("You find {$bonus} coins on the corpse. Score: {$this->player->score}");
        }
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
        $invText = empty($inv) ?
            '(empty)' :
            implode(', ', array_map(fn($weapon) => $weapon->name, $inv));

        $this->io->writeln("HP: {$this->player->health}");
        $this->io->writeln("Score: {$this->player->score}");
        $this->io->writeln("Inventory: {$invText}");
        return true;
    }

    private function map(): bool
    {
        $this->io->writeln($this->dungeon->renderVisitedMap($this->player->position->x, $this->player->position->y));
        $this->io->writeln("Legend: @=you, ?=unknown, E=empty, M=monster room, T=treasure room, X=exit");
        return true;
    }

    private function help(): bool
    {
        $this->io->writeln("Commands:");
        $this->io->writeln("  Movement: a/w/s/d (left/up/down/right)");
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
        $this->dungeon->markRoomVisited($this->player->position);

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
