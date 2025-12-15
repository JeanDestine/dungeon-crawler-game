<?php

namespace App\IO;

use App\Interfaces\IO;

final class CliIO implements IO
{
    public function write(string $text): void
    {
        echo $text;
    }

    public function writeln(string $text = ''): void
    {
        echo $text . PHP_EOL;
    }

    public function read(string $prompt = '> '): string
    {
        $this->write($prompt);
        $line = fgets(STDIN);
        if ($line === false) {
            return '';
        }

        return strtolower(trim($line));
    }
}
