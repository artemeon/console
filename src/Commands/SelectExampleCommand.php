<?php

declare(strict_types=1);

namespace Artemeon\Console\Commands;

use Artemeon\Console\Command;

use function Laravel\Prompts\spin;

class SelectExampleCommand extends Command
{
    protected string $signature = 'select';

    public function __invoke(): int
    {
        $this->select(label: 'Please choose a car brand', options: [
            'audi' => 'Audi',
            'bmw' => 'BMW',
            'ferrari' => 'Ferrari',
            'koenigsegg' => 'Koenigsegg',
            'seat' => 'Seat',
            'tesla' => 'Tesla',
            'vw' => 'Volkswagen',
        ]);

        return self::SUCCESS;
    }
}
