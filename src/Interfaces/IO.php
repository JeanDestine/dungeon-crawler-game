<?php

namespace App\Interfaces;

interface IO
{
    public function write(string $text): void;
    public function writeln(string $text = ''): void;

    /** Returns trimmed user input (lowercased). */
    public function read(string $prompt = '> '): string;
}
