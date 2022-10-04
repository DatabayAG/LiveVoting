<?php

declare(strict_types=1);

namespace LiveVoting\Context;

use ilLiveVotingPlugin;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

class xlvoRbacSystem
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;

    public function checkAccess(string $a_operations, int $a_ref_id, string $a_type = ""): bool
    {
        return false;
    }
}
