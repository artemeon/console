<?php

declare(strict_types=1);

namespace Artemeon\Console\Styles;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function Termwind\render;
use function Termwind\terminal;

class ArtemeonStyle extends SymfonyStyle
{
    private OutputInterface $output;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        parent::__construct($input, $output);

        $this->output = $output;
    }

    /**
     * @inheritDoc
     */
    public function title(string $message): void
    {
        parent::title($message);
    }

    /**
     * @inheritDoc
     */
    public function section(string $message): void
    {
        if ($this->output->isQuiet()) {
            return;
        }

        $width = terminal()->width();
        $length = strlen(' ' . $message . ' ');

        $this->writeln(' === ' . $message . ' ' . str_repeat('=', $width - $length - 5));
        $this->newLine();
    }

    /**
     * @inheritDoc
     */
    public function info(array | string $message): void
    {
        if ($this->output->isQuiet()) {
            return;
        }

        foreach ($this->transformMessage($message) as $m) {
            render(
                <<<HTML
<div class="mb-1 ml-1 px-1">
    <span class="bg-blue-500 text-white px-1 mr-4">INFO</span> $m
</div>
HTML
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function text(array | string $message): void
    {
        if ($this->output->isQuiet()) {
            return;
        }

        foreach ($this->transformMessage($message) as $m) {
            render(
                <<<HTML
<div class="mb-1 ml-1 px-1">
    $m
</div>
HTML
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function success(array | string $message): void
    {
        if ($this->output->isQuiet()) {
            return;
        }

        foreach ($this->transformMessage($message) as $m) {
            render(
                <<<HTML
<div class="mb-1 ml-1 px-1">
    <span class="bg-green-500 text-gray-900 px-1 mr-1">SUCCESS</span> $m
</div>
HTML
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function error(array | string $message): void
    {
        if ($this->output->isQuiet()) {
            return;
        }

        foreach ($this->transformMessage($message) as $m) {
            render(
                <<<HTML
<div class="mb-1 ml-1 px-1">
    <span class="bg-red-500 text-white px-1 mr-1">ERROR !</span> $m
</div>
HTML
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function warning(array | string $message): void
    {
        if ($this->output->isQuiet()) {
            return;
        }

        foreach ($this->transformMessage($message) as $m) {
            render(
                <<<HTML
<div class="mb-1 ml-1 px-1">
    <span class="bg-yellow-500 text-gray-900 px-1 pr-2 mr-1">! WARN</span> $m
</div>
HTML
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function note(array | string $message): void
    {
        if ($this->output->isQuiet()) {
            return;
        }

        foreach ($this->transformMessage($message) as $m) {
            render(
                <<<HTML
<div class="mb-1 ml-1 px-1">
    <span class="bg-yellow-500 text-gray-900 px-1 pr-2 mr-1">! NOTE</span> $m
</div>
HTML
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function caution(array | string $message): void
    {
        if ($this->output->isQuiet()) {
            return;
        }

        foreach ($this->transformMessage($message) as $m) {
            render(
                <<<HTML
<div class="mb-1 ml-1 px-1">
    <span class="bg-red-500 text-white px-1 mr-1">CAUTION</span> $m
</div>
HTML
            );
        }
    }

    private function transformMessage(array | string $message): array
    {
        return is_array($message) ? $message : [$message];
    }
}
