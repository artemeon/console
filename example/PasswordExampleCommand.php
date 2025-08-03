<?php

declare(strict_types=1);

namespace Artemeon\Console\Example;

use Artemeon\Console\Command;

class PasswordExampleCommand extends Command
{
    protected string $signature = 'password';

    public function __invoke(): int
    {
        $this->password('Choose your password', required: true);

        return self::SUCCESS;
    }
}
