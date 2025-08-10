<?php

declare(strict_types=1);

namespace Artemeon\Console\Concerns;

use Artemeon\Console\Clipboard;
use Artemeon\Console\Styles\ArtemeonStyle;
use Closure;
use Illuminate\Support\Collection;
use Laravel\Prompts\FormBuilder;
use RuntimeException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Terminal;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\form;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\password;
use function Laravel\Prompts\pause;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;
use function Termwind\terminal;

trait InteractsWithIO
{
    protected InputInterface $input;

    protected ArtemeonStyle $output;

    protected int $verbosity = OutputInterface::VERBOSITY_NORMAL;

    /**
     * @var array<string, int>
     */
    protected array $verbosityMap = [
        'v' => OutputInterface::VERBOSITY_VERBOSE,
        'vv' => OutputInterface::VERBOSITY_VERY_VERBOSE,
        'vvv' => OutputInterface::VERBOSITY_DEBUG,
        'quiet' => OutputInterface::VERBOSITY_QUIET,
        'normal' => OutputInterface::VERBOSITY_NORMAL,
    ];

    public function hasArgument(string $name): bool
    {
        return $this->input->hasArgument($name);
    }

    /**
     * Get the value of a command argument.
     *
     * @return array<string|bool|int|float|array<int,mixed>|null> | bool | string | null
     */
    public function argument(?string $key = null): array | bool | string | null
    {
        if ($key === null) {
            return $this->input->getArguments();
        }

        return $this->input->getArgument($key);
    }

    /**
     * Get all the arguments passed to the command.
     *
     * @return array<string|bool|int|float|array<int,mixed>|null>
     */
    public function arguments(): array
    {
        return (array) $this->argument();
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
     *
     * @return array<string|bool|int|float|array<int,mixed>|null> | bool | string | null
     */
    public function option(?string $key = null): array | bool | string | null
    {
        if (null === $key) {
            return $this->input->getOptions();
        }

        return $this->input->getOption($key);
    }

    /**
     * Get all the options passed to the command.
     *
     * @return array<string|bool|int|float|array<int,mixed>|null>
     */
    public function options(): array
    {
        return (array) $this->option();
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
        ?Closure $validate = null,
        string $hint = '',
        ?Closure $transform = null,
    ): bool {
        return confirm(
            label: $label,
            default: $default,
            yes: $yes,
            no: $no,
            required: $required,
            validate: $validate,
            hint: $hint,
            transform: $transform,
        );
    }

    public function ask(
        string $label,
        string $placeholder = '',
        string $default = '',
        bool | string $required = false,
        ?Closure $validate = null,
        string $hint = '',
        ?Closure $transform = null,
    ): string {
        return text(
            label: $label,
            placeholder: $placeholder,
            default: $default,
            required: $required,
            validate: $validate,
            hint: $hint,
            transform: $transform,
        );
    }

    public function textarea(
        string $label,
        string $placeholder = '',
        string $default = '',
        bool | string $required = false,
        ?Closure $validate = null,
        string $hint = '',
        int $rows = 5,
        ?Closure $transform = null,
    ): string {
        return textarea(
            label: $label,
            placeholder: $placeholder,
            default: $default,
            required: $required,
            validate: $validate,
            hint: $hint,
            rows: $rows,
            transform: $transform,
        );
    }

    /**
     * @param array<string>|Collection<int, string>|Closure(string): array<string> $options
     */
    public function suggest(
        string $label,
        array | Closure | Collection $options,
        string $placeholder = '',
        string $default = '',
        int $scroll = 5,
        bool | string $required = false,
        ?Closure $validate = null,
        string $hint = '',
        ?Closure $transform = null,
    ): string {
        return suggest(
            label: $label,
            options: $options,
            placeholder: $placeholder,
            default: $default,
            scroll: $scroll,
            required: $required,
            validate: $validate,
            hint: $hint,
            transform: $transform,
        );
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @param array<string>|Collection<int, string>|Closure(string): array<string> $options
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
        ?Closure $validate = null,
        string $hint = '',
        ?Closure $transform = null,
    ): string {
        return password(
            label: $label,
            placeholder: $placeholder,
            required: $required,
            validate: $validate,
            hint: $hint,
            transform: $transform,
        );
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
        ?Closure $validate = null,
    ): string {
        return $this->password($label, $placeholder, $required, $validate);
    }

    /**
     * Give the user a single choice from an array of answers.
     *
     * @param array<int|string, string>|Collection<int|string, string> $options
     */
    public function select(
        string $label,
        array | Collection $options,
        int | string | null $default = null,
        int $scroll = 5,
        ?Closure $validate = null,
        string $hint = '',
        string | true $required = true,
        ?Closure $transform = null,
    ): int | string {
        return select(
            label: $label,
            options: $options,
            default: $default,
            scroll: $scroll,
            validate: $validate,
            hint: $hint,
            required: $required,
            transform: $transform,
        );
    }

    /**
     * Give the user a single choice from an array of answers.
     *
     * @param array<int|string, string>|Collection<int|string, string> $options
     *
     * @deprecated Use {@see self::select} instead.
     */
    public function choice(
        string $label,
        array | Collection $options,
        int | string | null $default = null,
        int $scroll = 5,
        ?Closure $validate = null,
    ): int | string {
        return $this->select($label, $options, $default, $scroll, $validate);
    }

    /**
     * Prompt the user to select multiple options.
     *
     * @param array<int|string, string>|Collection<int|string, string> $options
     * @param array<int|string>|Collection<int, int|string> $default
     *
     * @return array<int | string>
     */
    public function multiselect(
        string $label,
        array | Collection $options,
        array | Collection $default = [],
        int $scroll = 5,
        bool | string $required = false,
        ?Closure $validate = null,
        string $hint = 'Use the space bar to select options.',
        ?Closure $transform = null,
    ): array {
        return multiselect(
            label: $label,
            options: $options,
            default: $default,
            scroll: $scroll,
            required: $required,
            validate: $validate,
            hint: $hint,
            transform: $transform,
        );
    }

    /**
     * Format input to textual table.
     *
     * @param string[] $headers
     * @param array<int, TableSeparator|array<int,mixed>> $rows
     * @param array<int, TableStyle|string> $columnStyles
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
     *
     * @template TReturn of mixed
     *
     * @param Closure(): TReturn $callback
     */
    public function spin(Closure $callback, string $message = ''): mixed
    {
        return spin($callback, $message);
    }

    /**
     * Allow the user to search for an option.
     */
    public function search(
        string $label,
        Closure $options,
        string $placeholder = '',
        int $scroll = 5,
        ?Closure $validate = null,
        string $hint = '',
        string | true $required = true,
        ?Closure $transform = null,
    ): int | string {
        return search(
            label: $label,
            options: $options,
            placeholder: $placeholder,
            scroll: $scroll,
            validate: $validate,
            hint: $hint,
            required: $required,
            transform: $transform,
        );
    }

    /**
     * Execute a given callback while advancing a progress bar.
     *
     * @template T of mixed
     *
     * @param int | iterable<T> $totalSteps
     *
     * @return int | iterable<T> | null
     */
    public function withProgressBar(int | iterable $totalSteps, Closure $callback): int | iterable | null
    {
        $bar = $this->output->createProgressBar(
            match (true) {
                is_int($totalSteps) => $totalSteps,
                is_countable($totalSteps) => count($totalSteps),
                is_iterable($totalSteps) => count(iterator_to_array($totalSteps)),
            },
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

    public function form(): FormBuilder
    {
        return form();
    }

    public function pause(string $message = 'Press enter to continue...'): void
    {
        pause($message);
    }

    public function clear(): void
    {
        clear();
    }

    public function clearScreen(): void
    {
        terminal()->clear();
    }

    public function terminalHeight(): int
    {
        return (new Terminal())->getHeight();
    }

    public function terminalWidth(): int
    {
        return (new Terminal())->getWidth();
    }

    /**
     * @param string[] $elements
     */
    public function listing(array $elements): void
    {
        $this->output->listing($elements);
    }

    /**
     * @param string[]|string $message
     */
    public function note(array | string $message): void
    {
        $this->output->note($message);
    }

    /**
     * @param string[]|string $message
     */
    public function caution(array | string $message): void
    {
        $this->output->caution($message);
    }

    /**
     * Write a string as an information output.
     *
     * @param string[]|string $message
     */
    public function text(array | string $message): void
    {
        $this->output->text($message);
    }

    /**
     * Write a string as an information output.
     *
     * @param string[]|string $message
     */
    public function info(array | string $message): void
    {
        $this->output->info($message);
    }

    public function line(string $message, ?string $style = null, int | string | null $verbosity = null): void
    {
        $styled = $style !== null && $style !== '' && $style !== '0' ? sprintf('<%s>%s</%s>', $style, $message, $style) : $message;

        $this->output->writeln($styled, $this->parseVerbosity($verbosity));
    }

    /**
     * Write a string as an error output.
     *
     * @param string[]|string $message
     */
    public function error(array | string $message): void
    {
        $this->output->error($message);
    }

    /**
     * Write a string as a warning output.
     *
     * @param string[]|string $message
     */
    public function warn(array | string $message): void
    {
        $this->output->warning($message);
    }

    /**
     * Write a string as a success output.
     *
     * @param string[]|string $message
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

    public function toClipboard(string $content, ?string $successMessage = null): static
    {
        try {
            $success = Clipboard::copy($content);
        } catch (RuntimeException) {
            return $this;
        }

        if ($success) {
            $this->success($successMessage ?? $content . ' copied to clipboard.');
        }

        return $this;
    }

    protected function parseVerbosity(int | string | null $level = null): int
    {
        if (isset($this->verbosityMap[$level])) {
            $level = $this->verbosityMap[$level];
        } elseif (!is_int($level)) {
            $level = $this->verbosity;
        }

        return $level;
    }

    protected function isWindows(): bool
    {
        return strtolower(PHP_OS_FAMILY) === 'windows';
    }
}
