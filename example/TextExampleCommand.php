<?php

declare(strict_types=1);

namespace Artemeon\Console\Example;

use Artemeon\Console\Command;

class TextExampleCommand extends Command
{
    protected string $signature = 'text';

    public function __invoke(): int
    {
        $username = $_SERVER['USER'] ?? '';

        $this->ask('What is your name?', 'E.g. your username', $username, true);

        return self::SUCCESS;
    }
}
