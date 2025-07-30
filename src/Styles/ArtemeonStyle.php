<?php

declare(strict_types=1);

namespace Artemeon\Console\Styles;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function Termwind\parse;
use function Termwind\terminal;

class ArtemeonStyle extends SymfonyStyle
{
    private readonly OutputInterface $output;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        parent::__construct($input, $output);

        $this->output = $output;
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
     *
     * @param string[]|string $message
     */
    public function info(array | string $message): void
    {
        if ($this->output->isQuiet()) {
            return;
        }

        foreach ($this->transformMessage($message) as $m) {
            $this->output->write(parse(
                <<<HTML
<div class="mb-1 ml-1 px-1">
    <span class="bg-blue-500 text-white px-1 mr-4">INFO</span> $m
</div>
HTML
            ));
        }
    }

    /**
     * @inheritDoc
     *
     * @param string[]|string $message
     */
    public function text(array | string $message): void
    {
        if ($this->output->isQuiet()) {
            return;
        }

        foreach ($this->transformMessage($message) as $m) {
            $this->output->write(parse(
                <<<HTML
<div class="mb-1 ml-1 px-1">
    $m
</div>
HTML
            ));
        }
    }

    /**
     * @inheritDoc
     *
     * @param string[]|string $message
     */
    public function success(array | string $message): void
    {
        if ($this->output->isQuiet()) {
            return;
        }

        foreach ($this->transformMessage($message) as $m) {
            $this->output->write(parse(
                <<<HTML
<div class="mb-1 ml-1 px-1">
    <span class="bg-green-500 text-gray-900 px-1 mr-1">SUCCESS</span> $m
</div>
HTML
            ));
        }
    }

    /**
     * @inheritDoc
     *
     * @param string[]|string $message
     */
    public function error(array | string $message): void
    {
        if ($this->output->isQuiet()) {
            return;
        }

        foreach ($this->transformMessage($message) as $m) {
            $this->output->write(parse(
                <<<HTML
<div class="mb-1 ml-1 px-1">
    <span class="bg-red-500 text-white px-1 mr-1">ERROR !</span> $m
</div>
HTML
            ));
        }
    }

    /**
     * @inheritDoc
     *
     * @param string[]|string $message
     */
    public function warning(array | string $message): void
    {
        if ($this->output->isQuiet()) {
            return;
        }

        foreach ($this->transformMessage($message) as $m) {
            $this->output->write(parse(
                <<<HTML
<div class="mb-1 ml-1 px-1">
    <span class="bg-yellow-500 text-gray-900 px-1 pr-2 mr-1">! WARN</span> $m
</div>
HTML
            ));
        }
    }

    /**
     * @inheritDoc
     *
     * @param string[]|string $message
     */
    public function note(array | string $message): void
    {
        if ($this->output->isQuiet()) {
            return;
        }

        foreach ($this->transformMessage($message) as $m) {
            $this->output->write(parse(
                <<<HTML
<div class="mb-1 ml-1 px-1">
    <span class="bg-yellow-500 text-gray-900 px-1 pr-2 mr-1">! NOTE</span> $m
</div>
HTML
            ));
        }
    }

    /**
     * @inheritDoc
     *
     * @param string[]|string $message
     */
    public function caution(array | string $message): void
    {
        if ($this->output->isQuiet()) {
            return;
        }

        foreach ($this->transformMessage($message) as $m) {
            $this->output->write(parse(
                <<<HTML
<div class="mb-1 ml-1 px-1">
    <span class="bg-red-500 text-white px-1 mr-1">CAUTION</span> $m
</div>
HTML
            ));
        }
    }

    /**
     * @param string[]|string $message
     *
     * @return string[]
     */
    private function transformMessage(array | string $message): array
    {
        return is_array($message) ? $message : [$message];
    }
}
