<?php

declare(strict_types=1);

namespace LiveVoting\Option;

use ilLiveVotingPlugin;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

/**
 * @deprecated
 */
class xlvoData
{
    use DICTrait;
    use LiveVotingTrait;

    /** @deprecated */
    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    /** @deprecated */
    public const TABLE_NAME = 'rep_robj_xlvo_data';
}
