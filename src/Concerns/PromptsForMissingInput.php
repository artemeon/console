<?php

declare(strict_types=1);

namespace Artemeon\Console\Concerns;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait PromptsForMissingInput
{
    /**
     * Interact with the user before validating the input.
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        parent::interact($input, $output);

        $this->promptForMissingArguments($input, $output);
    }

    /**
     * Prompt the user for any missing arguments.
     */
    protected function promptForMissingArguments(InputInterface $input, OutputInterface $output): void
    {
        $prompted = (new Collection($this->getDefinition()->getArguments()))
            ->reject(fn (InputArgument $argument): bool => $argument->getName() === 'command')
            ->filter(fn (InputArgument $argument): bool => $argument->isRequired() && match (true) {
                $argument->isArray() => $input->getArgument($argument->getName()) === [],
                default => $input->getArgument($argument->getName()) === null,
            })
            ->each(function (InputArgument $argument) use ($input): void {
                $description = rtrim(trim($argument->getDescription()), '!:,.?');
                $label = $this->promptForMissingArgumentsUsing()[$argument->getName()] ??
                    'What is ' . lcfirst($description === '' ? 'the ' . $argument->getName() : $description) . '?';

                if ($label instanceof Closure) {
                    $input->setArgument($argument->getName(), $argument->isArray() ? Arr::wrap($label()) : $label());

                    return;
                }

                if (is_array($label)) {
                    [$label, $placeholder] = $label;
                }

                $answer = $this->ask(
                    label: $label,
                    placeholder: $placeholder ?? '',
                    required: true,
                );

                $input->setArgument($argument->getName(), $argument->isArray() ? [$answer] : $answer);
            })
            ->isNotEmpty();

        if ($prompted) {
            $this->afterPromptingForMissingArguments($input, $output);
        }
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string, string>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [];
    }

    /**
     * Perform actions after the user was prompted for missing arguments.
     */
    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output): void
    {
    }

    /**
     * Whether the input contains any options that differ from the default values.
     */
    protected function didReceiveOptions(InputInterface $input): bool
    {
        return (new Collection($this->getDefinition()->getOptions()))
            ->reject(fn ($option): bool => $input->getOption($option->getName()) === $option->getDefault())
            ->isNotEmpty();
    }
}
