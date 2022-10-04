<?php

declare(strict_types=1);

namespace LiveVoting\Context;

use ilContextTemplate;
use ilLiveVotingPlugin;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

class xlvoContextLiveVoting implements ilContextTemplate
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;

    public static function supportsRedirects(): bool
    {
        return false;
    }

    public static function hasUser(): bool
    {
        return true;
    }

    public static function usesHTTP(): bool
    {
        return true;
    }

    public static function hasHTML(): bool
    {
        return true;
    }

    public static function usesTemplate(): bool
    {
        return true;
    }

    public static function initClient(): bool
    {
        return true;
    }

    public static function doAuthentication(): bool
    {
        return false;
    }

    public static function supportsPersistentSessions(): bool
    {
        return false;
    }

    public static function supportsPushMessages(): bool
    {
        return false;
    }

    public static function isSessionMainContext(): bool
    {
        return false;
    }

    public static function modifyHttpPath(string $httpPath): string
    {
        return $httpPath;
    }
}
