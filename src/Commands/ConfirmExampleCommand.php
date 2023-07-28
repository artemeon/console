<?php

declare(strict_types=1);

namespace Artemeon\Console\Commands;

use Artemeon\Console\Command;

class ConfirmExampleCommand extends Command
{
    protected string $signature = 'confirm';

    public function __invoke(): int
    {
        $this->confirm(label: 'Please confirm, sending me all your money', yes: 'Absolutely', no: 'Absolutely not');

        return self::SUCCESS;
    }
}
