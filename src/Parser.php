<?php

declare(strict_types=1);

namespace Artemeon\Console;

use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Parser
{
    /**
     * Parse the given console command definition into an array.
     *
     * @throws InvalidArgumentException
     * @return array{string, InputArgument[], InputOption[]}
     */
    public static function parse(string $expression): array
    {
        $name = static::name($expression);

        if (preg_match_all('/\{\s*(.*?)\s*\}/', $expression, $matches) && count($matches[1])) {
            return array_merge([$name], static::parameters($matches[1]));
        }

        return [$name, [], []];
    }

    /**
     * Extract the name of the command from the expression.
     *
     * @throws InvalidArgumentException
     */
    protected static function name(string $expression): string
    {
        if (!preg_match('/[^\s]+/', $expression, $matches)) {
            throw new InvalidArgumentException('Unable to determine command name from signature.');
        }

        return $matches[0];
    }

    /**
     * Extract all parameters from the tokens.
     *
     * @param string[] $tokens
     *
     * @return array{InputArgument[], InputOption[]}
     */
    protected static function parameters(array $tokens): array
    {
        $arguments = [];
        $options = [];

        foreach ($tokens as $token) {
            if (preg_match('/-{2,}(.*)/', $token, $matches)) {
                $options[] = static::parseOption($matches[1]);
            } else {
                $arguments[] = static::parseArgument($token);
            }
        }

        return [$arguments, $options];
    }

    /**
     * Parse an argument expression.
     */
    protected static function parseArgument(string $token): InputArgument
    {
        [$token, $description] = static::extractDescription($token);

        return match (true) {
            str_ends_with($token, '?*') => new InputArgument(trim($token, '?*'), InputArgument::IS_ARRAY, $description),
            str_ends_with($token, '*') => new InputArgument(
                trim($token, '*'),
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                $description,
            ),
            str_ends_with($token, '?') => new InputArgument(trim($token, '?'), InputArgument::OPTIONAL, $description),
            preg_match('/(.+)\=\*(.+)/', $token, $matches) => new InputArgument(
                $matches[1],
                InputArgument::IS_ARRAY,
                $description,
                preg_split('/,\s?/', $matches[2]),
            ),
            preg_match('/(.+)\=(.+)/', $token, $matches) => new InputArgument(
                $matches[1],
                InputArgument::OPTIONAL,
                $description,
                $matches[2],
            ),
            default => new InputArgument($token, InputArgument::REQUIRED, $description),
        };
    }

    /**
     * Parse an option expression.
     */
    protected static function parseOption(string $token): InputOption
    {
        [$token, $description] = static::extractDescription($token);

        $matches = preg_split('/\s*\|\s*/', $token, 2);

        if (isset($matches[1])) {
            [$shortcut, $token] = $matches;
        } else {
            $shortcut = null;
        }

        return match (true) {
            str_ends_with($token, '==') => new InputOption(
                trim($token, '='),
                $shortcut,
                InputOption::VALUE_REQUIRED,
                $description,
            ),
            str_ends_with($token, '=') => new InputOption(
                trim($token, '='),
                $shortcut,
                InputOption::VALUE_OPTIONAL,
                $description,
            ),
            str_ends_with($token, '==*') => new InputOption(
                trim($token, '=*'),
                $shortcut,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            ),
            str_ends_with($token, '=*') => new InputOption(
                trim($token, '=*'),
                $shortcut,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                $description,
            ),
            preg_match('/(.+)\=\*(.+)/', $token, $matches) === 1 => new InputOption(
                $matches[1],
                $shortcut,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                $description,
                preg_split('/,\s?/', $matches[2]),
            ),
            preg_match('/(.+)\=(.+)/', $token, $matches) === 1 => new InputOption(
                $matches[1],
                $shortcut,
                InputOption::VALUE_OPTIONAL,
                $description,
                $matches[2],
            ),
            str_ends_with($token, '!') => new InputOption(
                trim($token, '!'),
                $shortcut,
                InputOption::VALUE_NEGATABLE,
                $description,
            ),
            default => new InputOption($token, $shortcut, InputOption::VALUE_NONE, $description),
        };
    }

    /**
     * Parse the token into its token and description segments.
     *
     * @return array{string,string}|string[]
     */
    protected static function extractDescription(string $token): array
    {
        $parts = preg_split('/\s+:\s+/', trim($token), 2);
        if ($parts === false) {
            return [];
        }

        return count($parts) === 2 ? $parts : [$token, ''];
    }
}
