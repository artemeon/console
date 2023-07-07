<?php

declare(strict_types=1);

namespace Artemeon\Console\Concerns;

use Artemeon\Console\Style\ArtemeonStyle;
use Closure;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

trait InteractsWithIO
{
    protected InputInterface $input;
    protected ArtemeonStyle $output;
    protected int $verbosity = OutputInterface::VERBOSITY_NORMAL;
    protected array $verbosityMap = [
        'v' => OutputInterface::VERBOSITY_VERBOSE,
        'vv' => OutputInterface::VERBOSITY_VERY_VERBOSE,
        'vvv' => OutputInterface::VERBOSITY_DEBUG,
        'quiet' => OutputInterface::VERBOSITY_QUIET,
        'normal' => OutputInterface::VERBOSITY_NORMAL,
    ];

    public function hasArgument(int | string $name): bool
    {
        return $this->input->hasArgument($name);
    }

    /**
     * Get the value of a command argument.
     */
    public function argument(?string $key = null): array | bool | null | string
    {
        if ($key === null) {
            return $this->input->getArguments();
        }

        return $this->input->getArgument($key);
    }

    /**
     * Get all the arguments passed to the command.
     */
    public function arguments(): array
    {
        return $this->argument();
    }

    /**
     * Determine if the given option is present.
     */
    public function hasOption(string $name): bool
    {
        return $this->input->hasOption($name);
    }

    /**
     * Get the value of a command option.
     */
    public function option(?string $key = null): array | bool | null | string
    {
        if (is_null($key)) {
            return $this->input->getOptions();
        }

        return $this->input->getOption($key);
    }

    /**
     * Get all the options passed to the command.
     */
    public function options(): array
    {
        return $this->option();
    }

    public function title(string $message): void
    {
        $this->io->title($message);
    }

    public function section(string $message): void
    {
        $this->io->section($message);
    }

    /**
     * Confirm a question.
     */
    public function confirm(string $question, bool $default = false): bool
    {
        return $this->output->confirm($question, $default);
    }

    public function ask(string $question, ?string $default = null, callable $validator = null): mixed
    {
        return $this->output->ask($question, $default, $validator);
    }

    /**
     * Prompt the user for input with auto completion.
     */
    public function anticipate(string $question, array | callable $choices, ?string $default = null): mixed
    {
        return $this->askWithCompletion($question, $choices, $default);
    }

    /**
     * Prompt the user for input with auto completion.
     */
    public function askWithCompletion(string $question, array | callable $choices, ?string $default = null): mixed
    {
        $questionObject = new Question($question, $default);

        is_callable($choices)
            ? $questionObject->setAutocompleterCallback($choices)
            : $questionObject->setAutocompleterValues($choices);

        return $this->output->askQuestion($questionObject);
    }

    /**
     * Prompt the user for input but hide the answer from the console.
     */
    public function secret(string $question, bool $fallback = true): mixed
    {
        $questionObject = new Question($question);

        $questionObject->setHidden(true)->setHiddenFallback($fallback);

        return $this->output->askQuestion($questionObject);
    }

    /**
     * Give the user a single choice from an array of answers.
     */
    public function choice(string $question, array $choices, int | null | string $default = null, mixed $attempts = null, bool $multiple = false): array | string
    {
        $questionObject = new ChoiceQuestion($question, $choices, $default);

        $questionObject->setMaxAttempts($attempts)->setMultiselect($multiple);

        return $this->output->askQuestion($questionObject);
    }

    /**
     * Format input to textual table.
     */
    public function table(array $headers, array $rows, string | TableStyle $tableStyle = 'default', array $columnStyles = []): void
    {
        $table = new Table($this->output);

        $table->setHeaders($headers)->setRows($rows)->setStyle($tableStyle);

        foreach ($columnStyles as $columnIndex => $columnStyle) {
            $table->setColumnStyle($columnIndex, $columnStyle);
        }

        $table->render();
    }

    /**
     * Execute a given callback while advancing a progress bar.
     */
    public function withProgressBar(int | iterable $totalSteps, Closure $callback): mixed
    {
        $bar = $this->output->createProgressBar(
            is_iterable($totalSteps) ? count($totalSteps) : $totalSteps,
        );

        $bar->start();

        if (is_iterable($totalSteps)) {
            foreach ($totalSteps as $value) {
                $callback($value, $bar);

                $bar->advance();
            }
        } else {
            $callback($bar);
        }

        $bar->finish();

        if (is_iterable($totalSteps)) {
            return $totalSteps;
        }

        return null;
    }

    public function progressStart(int $max = 0): void
    {
        $this->io->progressStart($max);
    }

    public function progressAdvance(int $step = 1): void
    {
        $this->io->progressAdvance($step);
    }

    public function progressFinish(): void
    {
        $this->io->progressFinish();
    }

    public function listing(array $elements): void
    {
        $this->io->listing($elements);
    }

    public function note(array | string $message): void
    {
        $this->io->note($message);
    }

    public function caution(array | string $message): void
    {
        $this->io->caution($message);
    }

    /**
     * Write a string as information output.
     */
    public function text(array | string $message): void
    {
        $this->io->text($message);
    }

    /**
     * Write a string as information output.
     */
    public function info(array | string $message): void
    {
        $this->text($message);
    }

    public function line(string $message, ?string $style = null, int | null | string $verbosity = null): void
    {
        $styled = $style ? "<$style>$message</$style>" : $message;

        $this->output->writeln($styled, $this->parseVerbosity($verbosity));
    }

    /**
     * Write a string as error output.
     */
    public function error(array | string $message): void
    {
        $this->output->error($message);
    }

    /**
     * Write a string as warning output.
     */
    public function warn(array | string $message): void
    {
        $this->output->warning($message);
    }

    /**
     * Write a string as success output.
     */
    public function success(array | string $message): void
    {
        $this->io->success($message);
    }

    public function newLine(int $count = 1): static
    {
        $this->output->newLine($count);

        return $this;
    }

    protected function parseVerbosity(int | null | string $level = null): int
    {
        if (isset($this->verbosityMap[$level])) {
            $level = $this->verbosityMap[$level];
        } elseif (!is_int($level)) {
            $level = $this->verbosity;
        }

        return $level;
    }
}
