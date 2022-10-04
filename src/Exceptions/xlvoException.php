<?php

declare(strict_types=1);

namespace LiveVoting\Exceptions;

use ilException;
use ilLiveVotingPlugin;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

class xlvoException extends ilException
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;

    public function __construct(string $message, int $a_code = 0)
    {
        parent::__construct($message, $a_code);
    }
}
