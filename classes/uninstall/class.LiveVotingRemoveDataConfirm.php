<?php

declare(strict_types=1);

require_once __DIR__ . "/../../vendor/autoload.php";

use LiveVoting\Utils\LiveVotingTrait;
use srag\RemovePluginDataConfirm\LiveVoting\AbstractRemovePluginDataConfirm;

/**
 * Class LiveVotingRemoveDataConfirm
 *
 * @ilCtrl_isCalledBy LiveVotingRemoveDataConfirm: ilUIPluginRouterGUI
 */
class LiveVotingRemoveDataConfirm extends AbstractRemovePluginDataConfirm
{
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
}
