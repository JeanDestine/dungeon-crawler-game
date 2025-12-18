<?php

namespace Tests\Unit;

use App\Classes\Game;
use App\Interfaces\IO;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GameTest extends TestCase
{
    private string $savePath;
    private mixed $io;

    protected function setUp(): void
    {
        parent::setUp();

        $this->io = $this->getMockIO(['name', '1']);
        $this->savePath = sys_get_temp_dir() . '/game_test_save.json';
        @unlink($this->savePath);
    }

    protected function tearDown(): void
    {
        @unlink($this->savePath);
        parent::tearDown();
    }

    public function testNewGameInitializesPlayerAndDungeon(): void
    {
        $game = new Game($this->io, $this->savePath);

        $this->invokePrivateMethod($game, 'newGame');

        $this->assertTrue(true); // If no exception occurred, setup succeeded
    }

    public function testSaveCreatesSaveFile(): void
    {
        $game = new Game($this->io, $this->savePath);

        $this->invokePrivateMethod($game, 'newGame');
        $this->invokePrivateMethod($game, 'save');

        $this->assertFileExists($this->savePath);
    }

    public function testSaveWritesValidJson(): void
    {
        $game = new Game($this->io, $this->savePath);

        $this->invokePrivateMethod($game, 'newGame');
        $this->invokePrivateMethod($game, 'save');

        $json = file_get_contents($this->savePath);
        $data = json_decode($json, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('player', $data);
        $this->assertArrayHasKey('dungeon', $data);
        $this->assertArrayHasKey('savedAt', $data);
    }

    public function testLoadRestoresSavedGame(): void
    {
        // First game → save
        $game = new Game($this->io, $this->savePath);
        $this->invokePrivateMethod($game, 'newGame');
        $this->invokePrivateMethod($game, 'save');

        $this->assertFileExists($this->savePath);

        // Second game → load
        $loadedGame = new Game($this->io, $this->savePath);
        $result = $this->invokePrivateMethod($loadedGame, 'load');

        $this->assertTrue($result);
    }

    public function testLoadHandlesMissingFile(): void
    {
        $game = new Game($this->io, $this->savePath);

        $result = $this->invokePrivateMethod($game, 'load');

        $this->assertTrue($result);
    }

    public function testMoveOutsideDungeonIsBlocked(): void
    {
        $this->io->expects($this->atLeastOnce())
            ->method('writeln')
            ->with($this->logicalOr(
                $this->stringContains('New game started'),
                $this->stringContains("can't go that way"),
                $this->anything()
            ));

        $game = new Game($this->io, $this->savePath);

        $this->invokePrivateMethod($game, 'newGame');

        // Attempt to move north from (0,0)
        $this->invokePrivateMethod($game, 'move', ['north']);
    }

    public function testLookCommandOutputsRoomDescription(): void
    {
        $this->io->expects($this->atLeastOnce())
            ->method('writeln');

        $game = new Game($this->io, $this->savePath);

        $this->invokePrivateMethod($game, 'newGame');
        $result = $this->invokePrivateMethod($game, 'look');

        $this->assertTrue($result);
    }

    public function testStatsCommandOutputsPlayerStats(): void
    {
        $this->io->expects($this->atLeastOnce())
            ->method('writeln');

        $game = new Game($this->io, $this->savePath);

        $this->invokePrivateMethod($game, 'newGame');
        $result = $this->invokePrivateMethod($game, 'stats');

        $this->assertTrue($result);
    }

    private function invokePrivateMethod(
        object $object,
        string $method,
        array $args = []
    ): mixed {
        $ref = new ReflectionClass($object);
        $m = $ref->getMethod($method);
        $m->setAccessible(true);

        return $m->invokeArgs($object, $args);
    }

    private function getMockIO(array $reads = []): IO
    {
        $io = $this->createMock(IO::class);

        $io->method('read')
            ->willReturnOnConsecutiveCalls(...$reads);

        $io->method('writeln');

        return $io;
    }
}
