<?php

declare(strict_types=1);

namespace LiveVoting\GUI;

use ilLiveVotingPlugin;
use ilToolbarGUI;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

class xlvoToolbarGUI extends ilToolbarGUI
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;

    protected function applyAutoStickyToSingleElement(): void
    {
    }
}
