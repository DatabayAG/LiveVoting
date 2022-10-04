<?php

declare(strict_types=1);

namespace LiveVoting\Context;

use ilLiveVotingPlugin;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;
use ilObjUser;

class xlvoDummyUser54 extends ilObjUser implements xlvoDummyUser
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;

    public function __construct()
    {
    }

    public function getLanguage(): string
    {
        return self::dic()->language()->getLangKey();
    }

    public function getId(): int
    {
        return 13;
    }

    public function getPref(string $a_keyword): ?string
    {
        return null;
    }
}
