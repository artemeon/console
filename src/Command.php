<?php

declare(strict_types=1);

namespace Artemeon\Console;

use Artemeon\Console\Style\ArtemeonStyle;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends SymfonyCommand
{
    /**
     * The name and signature of the console command.
     */
    protected string $signature;

    /**
     * The console command name.
     */
    protected string $name;

    /**
     * The console command description.
     */
    protected ?string $description;

    private InputInterface $input;
    private ArtemeonStyle $io;

    public function __construct()
    {
        if (isset($this->signature)) {
            $this->configureUsingFluentDefinition();
        } else {
            parent::__construct($this->name);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->io = new ArtemeonStyle($input, $output);

        $method = method_exists($this, 'handle') ? 'handle' : '__invoke';

        return [$this, $method]();
    }

    protected function arguments(): array
    {
        return $this->input->getArguments();
    }

    protected function argument(string $name): mixed
    {
        return $this->input->getArgument($name);
    }

    protected function options(): array
    {
        return $this->input->getOptions();
    }

    protected function option(string $name): mixed
    {
        return $this->input->getOption($name);
    }

    protected function ask(string $question, string $default = null, callable $validator = null): mixed
    {
        return $this->io->ask($question, $default, $validator);
    }

    protected function secret(string $question, callable $validator = null): mixed
    {
        return $this->io->askHidden($question, $validator);
    }

    protected function title(string $message): void
    {
        $this->io->title($message);
    }

    protected function section(string $message): void
    {
        $this->io->section($message);
    }

    protected function text(array | string $message): void
    {
        $this->io->text($message);
    }

    protected function info(array | string $message): void
    {
        $this->text($message);
    }

    protected function error(array | string $message): void
    {
        $this->io->error($message);
    }

    protected function warn(array | string $message): void
    {
        $this->io->warning($message);
    }

    protected function listing(array $elements): void
    {
        $this->io->listing($elements);
    }

    protected function note(array | string $message): void
    {
        $this->io->note($message);
    }

    protected function caution(array | string $message): void
    {
        $this->io->caution($message);
    }

    protected function table(array $headers, array $rows): void
    {
        $this->io->table($headers, $rows);
    }

    protected function confirm(string $question, bool $default = true): bool
    {
        return $this->io->confirm($question, $default);
    }

    protected function choice(string $question, array $choices, mixed $default = null, bool $multiSelect = false): mixed
    {
        return $this->io->choice($question, $choices, $default, $multiSelect);
    }

    protected function newLine(int $count = 1): void
    {
        $this->io->newLine($count);
    }

    protected function progressStart(int $max = 0): void
    {
        $this->io->progressStart($max);
    }

    protected function progressAdvance(int $step = 1): void
    {
        $this->io->progressAdvance($step);
    }

    protected function progressFinish(): void
    {
        $this->io->progressFinish();
    }

    protected function configureUsingFluentDefinition(): void
    {
        [$name, $arguments, $options] = Parser::parse($this->signature);

        parent::__construct($this->name = $name);

        $this->getDefinition()->addArguments($arguments);
        $this->getDefinition()->addOptions($options);
    }
}
