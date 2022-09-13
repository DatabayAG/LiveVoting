<?php

declare(strict_types=1);

namespace LiveVoting\Option;

use ilLiveVotingPlugin;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

/**
 * Class xlvoOptionOld
 *
 * @package LiveVoting\Option
 *
 * @deprecated
 */
class xlvoOptionOld
{
    use DICTrait;
    use LiveVotingTrait;
    /**
     * @var string
     *
     * @deprecated
     */
    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    /**
     * @var string
     *
     * @deprecated
     */
    public const TABLE_NAME = 'rep_robj_xlvo_option';
}
