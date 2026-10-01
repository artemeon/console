<p align="center">
    <img src=".github/header.svg" alt="Artemeon Console" width="100%">
</p>

<p align="center">
    <a href="https://packagist.org/packages/artemeon/console"><img src="https://img.shields.io/packagist/v/artemeon/console?style=for-the-badge" alt="Packagist Version"></a>
    <a href="phpstan.neon"><img src="https://img.shields.io/badge/PHPStan-level%208-brightgreen?style=for-the-badge" alt="PHPStan Level 8"></a>
    <a href="LICENSE"><img src="https://img.shields.io/packagist/l/artemeon/console?style=for-the-badge" alt="License"></a>
</p>

Wrapper library around `symfony/console` providing a customized console styling and some helpers.

It brings a Laravel-style command API to any Symfony Console application: signature-based input definitions, interactive prompts powered by [Laravel Prompts](https://github.com/laravel/prompts) and Termwind-styled output, without requiring the Laravel framework.

## Requirements

- PHP 8.3 or higher
- `symfony/console` 5, 6, 7 or 8

## Installation

```shell
composer require artemeon/console
```

## Quick Start

Extend `Artemeon\Console\Command`, define a signature and implement `handle()` (or `__invoke()`):

```php
use Artemeon\Console\Command;

class GreetCommand extends Command
{
    protected string $signature = 'greet {name : The name of the person} {--y|yell : Print the greeting in uppercase}';

    protected ?string $description = 'Greet someone';

    public function handle(): int
    {
        $greeting = 'Hello, ' . $this->argument('name') . '!';

        $this->info($this->option('yell') ? strtoupper($greeting) : $greeting);

        return self::SUCCESS;
    }
}
```

Register it like any other Symfony command:

```php
use Symfony\Component\Console\Application;

$app = new Application();
$app->add(new GreetCommand());
$app->run();
```

## Defining Commands

The following properties can be set on a command:

| Property       | Description                                                  |
|----------------|--------------------------------------------------------------|
| `$signature`   | Name, arguments and options of the command (see below).      |
| `$name`        | Command name, used when no `$signature` is defined.          |
| `$description` | Short description shown in the command list.                 |
| `$help`        | Help text shown by `help <command>`.                         |
| `$aliases`     | Alternative names for the command.                           |
| `$hidden`      | Hide the command from the command list.                      |

### Signature Syntax

Arguments and options are wrapped in curly braces. A description can be added after ` : `.

| Syntax                     | Meaning                                         |
|----------------------------|-------------------------------------------------|
| `{user}`                   | Required argument                               |
| `{user?}`                  | Optional argument                               |
| `{user=admin}`             | Optional argument with default value            |
| `{users*}`                 | Required array argument                         |
| `{users?*}`                | Optional array argument                         |
| `{users=*admin,guest}`     | Array argument with default values              |
| `{--force}`                | Flag (no value)                                 |
| `{--f\|force}`             | Flag with shortcut                              |
| `{--queue=}`               | Option with optional value                      |
| `{--queue==}`              | Option with required value                      |
| `{--queue=default}`        | Option with default value                       |
| `{--id=*}`                 | Array option with optional values               |
| `{--id==*}`                | Array option with required values               |
| `{--id=*1,2}`              | Array option with default values                |
| `{--ansi!}`                | Negatable option (`--ansi` / `--no-ansi`)       |
| `{user : The user's name}` | Any parameter with a description                |

### Without a Signature

Instead of a signature, you can set `$name` and return Symfony input definitions:

```php
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

protected string $name = 'greet';

protected function getArguments(): array
{
    return [new InputArgument('name', InputArgument::REQUIRED, 'The name of the person')];
}

protected function getOptions(): array
{
    return [new InputOption('yell', 'y', InputOption::VALUE_NONE, 'Print the greeting in uppercase')];
}
```

### Accessing Input

```php
$this->argument('name');   // single argument
$this->arguments();        // all arguments
$this->hasArgument('name');

$this->option('yell');     // single option
$this->options();          // all options
$this->hasOption('yell');
```

### Prompting for Missing Arguments

When a required argument is missing and the terminal is interactive, the user is prompted for it automatically. The question is derived from the argument's description (`What is the name of the person?`). You can customize the questions:

```php
protected function promptForMissingArgumentsUsing(): array
{
    return [
        'name' => 'Who should be greeted?',
        'email' => ['What is the email address?', 'E.g. jane@example.com'], // label and placeholder
        'role' => fn () => $this->select('Which role?', ['admin', 'editor']), // custom prompt
    ];
}
```

Override `afterPromptingForMissingArguments()` to run logic once all missing arguments are collected.

## Output

The command output uses `ArtemeonStyle`, a Termwind-based style with labeled message blocks:

```php
$this->title('Deployment');
$this->section('Assets');

$this->info('Fetching 128 records');
$this->success('Import finished');
$this->warn('3 records skipped');
$this->error('Connection failed');
$this->note('Remember to clear the cache');
$this->caution('This cannot be undone');

$this->text('Plain text');
$this->line('A line', 'comment');
$this->listing(['First', 'Second']);
$this->newLine();

$this->table(['ID', 'Name'], [[1, 'Jane'], [2, 'John']]);
$this->grid(['apple', 'banana', 'cherry']);
$this->callout('Heads up', 'A new version is available.');
```

## Prompts

All prompts from Laravel Prompts are available as methods. They fall back to Symfony questions on Windows and are skipped when the input is non-interactive.

```php
$name = $this->ask('What is your name?', placeholder: 'E.g. Jane', required: true);
$bio = $this->textarea('Tell us about yourself');
$password = $this->password('Password');
$age = $this->number('How old are you?', min: 0, max: 150);

$confirmed = $this->confirm('Do you want to continue?');

$brand = $this->select('Choose a car brand', ['audi' => 'Audi', 'bmw' => 'BMW']);
$toppings = $this->multiselect('Choose toppings', ['cheese', 'ham', 'olives']);

$city = $this->suggest('City', ['Berlin', 'Hamburg', 'Munich']);
$city = $this->autocomplete('City', ['Berlin', 'Hamburg', 'Munich']);
$userId = $this->search('Search a user', fn (string $value) => findUsers($value));

$row = $this->dataTable(['ID', 'Name'], [[1, 'Jane'], [2, 'John']]);

$this->pause();
```

`choice()`, `secret()`, `anticipate()` and `askWithCompletion()` are kept as aliases for Laravel-style compatibility. For multi-step input use `$this->form()`.

## Progress and Tasks

```php
use Laravel\Prompts\Support\Logger;

// Spinner while a callback runs
$result = $this->spin(fn () => fetchData(), 'Fetching data...');

// Task with spinner and live log output
$this->task('Running migrations', function (Logger $logger) {
    $logger->line('Migrating users table');
});

// Progress bar over an iterable or a number of steps
$this->withProgressBar($users, fn (User $user) => $user->sync());

// Manual progress bar
$this->progressStart(100);
$this->progressAdvance();
$this->progressFinish();

// Streamed text output
$this->stream();
```

## Utilities

```php
$this->toClipboard($token, 'Token copied to clipboard.'); // macOS, Linux (xclip) and Windows
$this->notify('Build finished', 'All tests passed');      // desktop notification (macOS and Linux)
$this->terminalTitle('Deploying...');

$this->terminalWidth();
$this->terminalHeight();
$this->clear();
$this->clearScreen();
```

`Artemeon\Console\Clipboard::copy()` can also be used on its own.

### Post Call Hook

Register a closure that runs after every command with the command's exit code, e.g. for logging or telemetry:

```php
use Artemeon\Console\Command;

Command::setupPostCallClosure(function (int $exitCode): void {
    // ...
});
```

## Examples

The [`example`](example) directory contains runnable example commands:

```shell
php example/example text
php example/example confirm
php example/example select
php example/example multiselect
php example/example password
```

## Development

```shell
composer test                 # run the test suite
composer test:type-coverage   # check type coverage
composer phpstan              # static analysis
composer pint                 # check code style
composer refactor             # run Rector
```

## License

The Artemeon Console Library is open-sourced software licensed under the [MIT license](LICENSE).
