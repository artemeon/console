<?php

declare(strict_types=1);

namespace Artemeon\Console;

use Artemeon\Console\Style\ArtemeonStyle;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends SymfonyCommand
{
    use Concerns\HasParameters;
    use Concerns\InteractsWithIO;

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
    protected ?string $description = null;

    /**
     * The console command help text.
     */
    protected ?string $help = null;

    /**
     * Indicates whether the command should be shown in the command list.
     */
    protected bool $hidden = false;

    /**
     * The console command name aliases.
     */
    protected array $aliases;

    private ArtemeonStyle $io;

    public function __construct()
    {
        if (isset($this->signature)) {
            $this->configureUsingFluentDefinition();
        } else {
            parent::__construct($this->name);
        }

        if (!isset($this->description)) {
            $this->setDescription((string) static::getDefaultDescription());
        } else {
            $this->setDescription((string) $this->description);
        }

        $this->setHelp((string) $this->help);

        $this->setHidden($this->isHidden());

        if (isset($this->aliases)) {
            $this->setAliases($this->aliases);
        }

        if (!isset($this->signature)) {
            $this->specifyParameters();
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->io = new ArtemeonStyle($input, $output);

        $method = method_exists($this, 'handle') ? 'handle' : '__invoke';

        return [$this, $method]();
    }

    protected function configureUsingFluentDefinition(): void
    {
        [$name, $arguments, $options] = Parser::parse($this->signature);

        parent::__construct($this->name = $name);

        $this->getDefinition()->addArguments($arguments);
        $this->getDefinition()->addOptions($options);
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden = true): static
    {
        parent::setHidden($this->hidden = $hidden);

        return $this;
    }
}
