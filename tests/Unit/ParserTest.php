<?php

declare(strict_types=1);

use Artemeon\Console\Parser;

covers(Parser::class);

it('can parse the name', function (): void {
    [$name] = Parser::parse('foo:bar');

    expect($name)->toBe('foo:bar');
});

it('throws an exception if the name is invalid', function (): void {
    expect(static fn () => Parser::parse(''))
        ->toThrow(InvalidArgumentException::class, 'Unable to determine command name from signature.');
});

it('can parse simple arguments', function (): void {
    [, $arguments] = Parser::parse('foo:bar {hello} {world}');

    expect($arguments)
        ->toHaveCount(2)
        ->and($arguments[0]->getName())->toBe('hello')
        ->and($arguments[0]->isRequired())->toBeTrue()
        ->and($arguments[0]->getDescription())->toBe('')
        ->and($arguments[1]->getName())->toBe('world')
        ->and($arguments[1]->isRequired())->toBeTrue()
        ->and($arguments[1]->getDescription())->toBe('');
});

it('can parse optional arguments', function (): void {
    [, $arguments] = Parser::parse('foo:bar {hello?} {world?}');

    expect($arguments)
        ->toHaveCount(2)
        ->and($arguments[0]->getName())->toBe('hello')
        ->and($arguments[0]->isRequired())->toBeFalse()
        ->and($arguments[1]->getName())->toBe('world')
        ->and($arguments[1]->isRequired())->toBeFalse();
});

it('can parse optional arguments with default value', function (): void {
    [, $arguments] = Parser::parse('foo:bar {hello=foo} {world=bar}');

    expect($arguments)
        ->toHaveCount(2)
        ->and($arguments[0]->getName())->toBe('hello')
        ->and($arguments[0]->isRequired())->toBeFalse()
        ->and($arguments[0]->getDefault())->toBe('foo')
        ->and($arguments[1]->getName())->toBe('world')
        ->and($arguments[1]->isRequired())->toBeFalse()
        ->and($arguments[1]->getDefault())->toBe('bar');
});

it('can parse arguments with description', function (): void {
    [, $arguments] = Parser::parse('foo:bar {hello : Foo} {world :  Bar : Baz        }');

    expect($arguments)
        ->toHaveCount(2)
        ->and($arguments[0]->getName())->toBe('hello')
        ->and($arguments[0]->isRequired())->toBeTrue()
        ->and($arguments[0]->getDescription())->toBe('Foo')
        ->and($arguments[1]->getName())->toBe('world')
        ->and($arguments[1]->isRequired())->toBeTrue()
        ->and($arguments[1]->getDescription())->toBe('Bar : Baz');
});

it('can parse simple options', function (): void {
    [,, $options] = Parser::parse('foo:bar {--hello} {--world}');

    expect($options)
        ->toHaveCount(2)
        ->and($options[0]->getName())->toBe('hello')
        ->and($options[0]->isArray())->toBeFalse()
        ->and($options[0]->getDefault())->toBeFalse()
        ->and($options[1]->getName())->toBe('world')
        ->and($options[1]->isArray())->toBeFalse()
        ->and($options[1]->getDefault())->toBeFalse();
});

it('can parse options with shortcut', function (): void {
    [,, $options] = Parser::parse('foo:bar {--h|hello} {--w|world}');

    expect($options)
        ->toHaveCount(2)
        ->and($options[0]->getName())->toBe('hello')
        ->and($options[0]->isArray())->toBeFalse()
        ->and($options[0]->getDefault())->toBeFalse()
        ->and($options[0]->getShortcut())->toBe('h')
        ->and($options[1]->getName())->toBe('world')
        ->and($options[1]->isArray())->toBeFalse()
        ->and($options[1]->getDefault())->toBeFalse()
        ->and($options[1]->getShortcut())->toBe('w');
});
