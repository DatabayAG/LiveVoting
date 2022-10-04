<?php

declare(strict_types=1);

namespace LiveVoting\Context;

use srag\DIC\LiveVoting\DICTrait;
use LiveVoting\Utils\LiveVotingTrait;
use ilLiveVotingPlugin;
use ilObjUser;

class xlvoDummyUser6 extends ilObjUser implements xlvoDummyUser
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

    public function getPref(string $a_keyword): string
    {
        return '';
    }
}
