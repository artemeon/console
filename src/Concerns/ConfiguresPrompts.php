<?php

declare(strict_types=1);

namespace Artemeon\Console\Concerns;

use Closure;
use Laravel\Prompts\ConfirmPrompt;
use Laravel\Prompts\MultiSearchPrompt;
use Laravel\Prompts\MultiSelectPrompt;
use Laravel\Prompts\PasswordPrompt;
use Laravel\Prompts\PausePrompt;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\SearchPrompt;
use Laravel\Prompts\SelectPrompt;
use Laravel\Prompts\SuggestPrompt;
use Laravel\Prompts\TextareaPrompt;
use Laravel\Prompts\TextPrompt;

trait ConfiguresPrompts
{
    protected function configurePrompts(): void
    {
        Prompt::setOutput($this->output);

        Prompt::interactive($this->input->isInteractive() && defined('STDIN') && stream_isatty(STDIN));

        Prompt::fallbackWhen($this->isWindows());

        TextPrompt::fallbackUsing(fn (TextPrompt $prompt): string => $this->promptUntilValid(
            fn (): mixed => $this->output->ask($prompt->label, $prompt->default !== '' ? $prompt->default : null) ?? '',
            $prompt->required,
            $prompt->validate,
        ));

        TextareaPrompt::fallbackUsing(fn (TextareaPrompt $prompt): string => $this->promptUntilValid(
            fn (): mixed => $this->output->ask($prompt->label, $prompt->default !== '' ? $prompt->default : null) ?? '',
            $prompt->required,
            $prompt->validate,
        ));

        PasswordPrompt::fallbackUsing(fn (PasswordPrompt $prompt): string => $this->promptUntilValid(
            fn (): mixed => $this->output->askHidden($prompt->label) ?? '',
            $prompt->required,
            $prompt->validate,
        ));

        PausePrompt::fallbackUsing(fn (PausePrompt $prompt): string => $this->promptUntilValid(
            function () use ($prompt): string {
                $this->output->askHidden($prompt->message);

                return (string) $prompt->value();
            },
            $prompt->required,
            $prompt->validate,
        ));

        ConfirmPrompt::fallbackUsing(fn (ConfirmPrompt $prompt): string => $this->promptUntilValid(
            fn (): bool => $this->output->confirm($prompt->label, $prompt->default),
            $prompt->required,
            $prompt->validate,
        ));

        SelectPrompt::fallbackUsing(fn (SelectPrompt $prompt): string => $this->promptUntilValid(
            fn (): int | string => $this->selectFallback($prompt->label, $prompt->options, $prompt->default),
            $prompt->required,
            $prompt->validate,
        ));

        MultiSelectPrompt::fallbackUsing(fn (MultiSelectPrompt $prompt): array => $this->promptUntilValid(
            fn (): array => $this->multiselectFallback($prompt->label, $prompt->options, $prompt->default, $prompt->required),
            $prompt->required,
            $prompt->validate,
        ));

        SuggestPrompt::fallbackUsing(fn (SuggestPrompt $prompt): string => $this->promptUntilValid(
            fn () => $this->askWithCompletion($prompt->label, $prompt->options, $prompt->default !== '' ? $prompt->default : null) ?? '',
            $prompt->required,
            $prompt->validate,
        ));

        SearchPrompt::fallbackUsing(fn (SearchPrompt $prompt): string => $this->promptUntilValid(
            function () use ($prompt) {
                $query = $this->output->ask($prompt->label);

                $options = ($prompt->options)($query);

                return $this->selectFallback($prompt->label, $options);
            },
            $prompt->required,
            $prompt->validate,
        ));

        MultiSearchPrompt::fallbackUsing(fn (MultiSearchPrompt $prompt) => $this->promptUntilValid(
            function () use ($prompt) {
                $query = $this->output->ask($prompt->label);

                $options = ($prompt->options)($query);

                return $this->multiselectFallback($prompt->label, $options, required: $prompt->required);
            },
            $prompt->required,
            $prompt->validate,
        ));
    }

    private function promptUntilValid(Closure $prompt, bool | string $required, ?Closure $validate): mixed
    {
        while (true) {
            $result = $prompt();

            if ($required && ($result === '' || $result === [] || $result === false)) {
                $this->output->error(is_string($required) ? $required : 'Required.');

                continue;
            }

            $error = $validate instanceof Closure ? $validate($result) : null;

            if (is_string($error) && $error !== '') {
                $this->output->error($error);

                continue;
            }

            return $result;
        }
    }

    /**
     * @param array<int | string> $options
     */
    private function selectFallback(string $label, array $options, int | string | null $default = null): int | string
    {
        $answer = $this->output->choice($label, $options, $default);

        if ($answer === (string) (int) $answer && !array_is_list($options)) {
            return (int) $answer;
        }

        return $answer;
    }

    /**
     * @param array<int | string> | array<array-key, int | string> $options
     * @param array<int | string> | array<array-key, int | string> $default
     *
     * @return array<int | string>
     */
    private function multiselectFallback(string $label, array $options, array $default = [], bool | string $required = false): array
    {
        $normalizedDefault = $default !== [] ? implode(',', $default) : null;

        if ($required === false) {
            $options = array_is_list($options)
                ? ['None', ...$options]
                : ['' => 'None'] + $options;

            if ($normalizedDefault === null) {
                $normalizedDefault = 'None';
            }
        }

        $answers = $this->output->choice($label, $options, $normalizedDefault, true);

        if (!array_is_list($options)) {
            $answers = array_map(static fn (string $value): mixed => $value === (string) (int) $value ? (int) $value : $value, $answers);
        }

        if ($required === false) {
            return array_is_list($options)
                ? array_values(array_filter($answers, static fn ($value): bool => $value !== 'None'))
                : array_filter($answers, static fn ($value): bool => $value !== '');
        }

        return $answers;
    }
}
