<?php

declare(strict_types=1);

namespace Artemeon\Console\Example;

use Artemeon\Console\Command;

class MultiSelectExampleCommand extends Command
{
    protected string $signature = 'multiselect';

    public function __invoke(): int
    {
        $this->multiselect(label: 'Please choose your favorite car brands', options: [
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
