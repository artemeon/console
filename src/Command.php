<?php

declare(strict_types=1);

namespace Artemeon\Console;

use Artemeon\Console\Styles\ArtemeonStyle;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends SymfonyCommand
{
    use Concerns\HasParameters;
    use Concerns\InteractsWithIO;
    use Concerns\ConfiguresPrompts;
    use Concerns\PromptsForMissingInput;

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
     *
     * @var string[]
     */
    protected array $aliases;

    public function __construct()
    {
        if (isset($this->signature)) {
            $this->configureUsingFluentDefinition();
        } else {
            parent::__construct($this->name);
        }

        if ($this->description !== null) {
            $this->setDescription($this->description);
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
        $this->output = new ArtemeonStyle($input, $output);

        $this->configurePrompts();

        $method = method_exists($this, 'handle') ? 'handle' : '__invoke';

        $callable = [$this, $method];
        if (!is_callable($callable)) {
            return self::FAILURE;
        }

        return $callable();
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
