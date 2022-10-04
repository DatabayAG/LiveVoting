<?php

declare(strict_types=1);

namespace LiveVoting\Context;

require_once 'include/inc.ilias_version.php';

use Exception;
use ilLiveVotingPlugin;
use LiveVoting\User\xlvoUser;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;
use ilException;

final class InitialisationManager
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;

    private function __construct()
    {
    }

    /**
     * Starts ILIAS without user and rbag management.
     * Languages, templates, error handling and database are fully loaded.
     *
     * @throws ilException   Thrown if no compatible ILIAS version could be found.
     */
    public static function startMinimal(): void
    {
        switch (true) {
            case self::version()->is7():
                Initialisation\Version\v7\xlvoBasicInitialisation::init();
                break;
            case self::version()->is6():
                Initialisation\Version\v6\xlvoBasicInitialisation::init();
                break;
            default:
                throw new ilException("Can't find bootstrap code for the given ILIAS version.");
        }

        xlvoUser::getInstance()->setIdentifier(session_id())->setType(xlvoUser::TYPE_PIN);
    }

    /**
     * Optimised ILIAS start with user management.
     *
     * @throws ilException When the user object is invalid.
     */
    public static function startLight(): void
    {
        xlvoInitialisation::init();

        if (!(self::dic()->user() instanceof xlvoDummyUser) && self::dic()->user()->getId()) {
            xlvoUser::getInstance()->setIdentifier(self::dic()->user()->getId())->setType(xlvoUser::TYPE_ILIAS);

            return;
        }

        throw new ilException("ILIAS light start failed because the user management returned an invalid user object.");
    }
}
