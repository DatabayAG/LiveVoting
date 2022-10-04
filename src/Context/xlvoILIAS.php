<?php

declare(strict_types=1);

namespace LiveVoting\Context;

use ilException;
use ilLiveVotingPlugin;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

class xlvoILIAS
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;

    public function __construct()
    {
    }

    public function getSetting(string $key): ?string
    {
        return self::dic()->settings()->get($key);
    }

    public function raiseError(string $a_msg, $a_err_obj): void
    {
        throw new ilException($a_msg);
    }
}
