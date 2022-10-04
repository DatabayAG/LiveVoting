<?php

declare(strict_types=1);

namespace LiveVoting\User;

use ilLiveVotingPlugin;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

class xlvoUser
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    public const TYPE_ILIAS = 1;
    public const TYPE_PIN = 2;
    protected static self $instance;
    protected int $type = self::TYPE_ILIAS;
    protected string $identifier = '';

    protected function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function isILIASUser(): bool
    {
        return ($this->getType() === self::TYPE_ILIAS);
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function isPINUser(): bool
    {
        return ($this->getType() === self::TYPE_PIN);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }
}
