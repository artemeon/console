<?php

declare(strict_types=1);

namespace Artemeon\Console\Concerns;

use Artemeon\Console\Styles\ArtemeonStyle;
use Closure;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\password;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\text;

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
        $this->output->title($message);
    }

    public function section(string $message): void
    {
        $this->output->section($message);
    }

    /**
     * Confirm a question.
     */
    public function confirm(
        string $label,
        bool $default = true,
        string $yes = 'Yes',
        string $no = 'No',
        bool | string $required = false,
        Closure $validate = null,
    ): bool {
        return confirm(label: $label, default: $default, yes: $yes, no: $no, required: $required, validate: $validate);
    }

    public function ask(
        string $label,
        string $placeholder = '',
        string $default = '',
        bool | string $required = false,
        Closure $validate = null,
    ): string {
        return text(
            label: $label,
            placeholder: $placeholder,
            default: $default,
            required: $required,
            validate: $validate
        );
    }

    public function suggest(
        string $label,
        array | Closure | Collection $options,
        string $placeholder = '',
        string $default = '',
        int $scroll = 5,
        bool | string $required = false,
    ): string {
        return suggest($label, $options, $placeholder, $default, $scroll, $required);
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @deprecated Use {@see self::suggest()} instead.
     */
    public function anticipate(string $label, array | Closure | Collection $options, string $default = ''): string
    {
        return $this->suggest($label, $options, default: $default);
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @deprecated
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
     * Prompt the user for input, hiding the value.
     */
    public function password(
        string $label,
        string $placeholder = '',
        bool | string $required = false,
        Closure $validate = null,
    ): string {
        return password($label, $placeholder, $required, $validate);
    }

    /**
     * Prompt the user for input but hide the answer from the console.
     *
     * @deprecated Use {@see self::password()} instead.
     */
    public function secret(
        string $label,
        string $placeholder = '',
        bool | string $required = false,
        Closure $validate = null,
    ): string {
        return $this->password($label, $placeholder, $required, $validate);
    }

    /**
     * Give the user a single choice from an array of answers.
     */
    public function select(
        string $label,
        array | Collection $options,
        int | string $default = null,
        int $scroll = 5,
        Closure $validate = null,
    ): int | string {
        return select($label, $options, $default, $scroll, $validate);
    }

    /**
     * Give the user a single choice from an array of answers.
     *
     * @deprecated Use {@see self::select} instead.
     */
    public function choice(
        string $label,
        array | Collection $options,
        int | string $default = null,
        int $scroll = 5,
        Closure $validate = null,
    ): int | string {
        return $this->select($label, $options, $default, $scroll, $validate);
    }

    /**
     * Prompt the user to select multiple options.
     *
     * @return array<int | string>
     */
    public function multiselect(
        string $label,
        array | Collection $options,
        array | Collection $default = [],
        int $scroll = 5,
        bool | string $required = false,
        Closure $validate = null,
    ): array {
        return multiselect($label, $options, $default, $scroll, $required, $validate);
    }

    /**
     * Format input to textual table.
     */
    public function table(
        array $headers,
        array $rows,
        string | TableStyle $tableStyle = 'default',
        array $columnStyles = [],
    ): void {
        $table = new Table($this->output);

        $table->setHeaders($headers)->setRows($rows)->setStyle($tableStyle);

        foreach ($columnStyles as $columnIndex => $columnStyle) {
            $table->setColumnStyle($columnIndex, $columnStyle);
        }

        $table->render();
    }

    /**
     * Render a spinner while the given callback is executing.
     */
    public function spin(Closure $callback, string $message = ''): mixed
    {
        return spin($callback, $message);
    }

    /**
     * Allow the user to search for an option.
     */
    public function search(string $label, Closure $options, string $placeholder = '', int $scroll = 5): int | string
    {
        return search($label, $options, $placeholder, $scroll);
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
        $this->output->progressStart($max);
    }

    public function progressAdvance(int $step = 1): void
    {
        $this->output->progressAdvance($step);
    }

    public function progressFinish(): void
    {
        $this->output->progressFinish();
    }

    public function listing(array $elements): void
    {
        $this->output->listing($elements);
    }

    public function note(array | string $message): void
    {
        $this->output->note($message);
    }

    public function caution(array | string $message): void
    {
        $this->output->caution($message);
    }

    /**
     * Write a string as information output.
     */
    public function text(array | string $message): void
    {
        $this->output->text($message);
    }

    /**
     * Write a string as information output.
     */
    public function info(array | string $message): void
    {
        $this->output->info($message);
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
        $this->output->success($message);
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
