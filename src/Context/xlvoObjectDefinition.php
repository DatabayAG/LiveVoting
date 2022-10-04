<?php

declare(strict_types=1);

namespace LiveVoting\Context;

use ilLiveVotingPlugin;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

class xlvoObjectDefinition
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;

    public function isRBACObject(): bool
    {
        return false;
    }

    public function getTranslationType(): string
    {
        return ''; //"sys"
    }

    public function getOrgUnitPermissionTypes(): array
    {
        return [];
    }
}
