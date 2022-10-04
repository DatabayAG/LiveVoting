<?php

declare(strict_types=1);

namespace LiveVoting\Utils;

use LiveVoting\Access\Access;
use LiveVoting\Access\Ilias;

trait LiveVotingTrait
{
    protected static function access(): Access
    {
        return Access::getInstance();
    }

    protected static function ilias(): Ilias
    {
        return Ilias::getInstance();
    }
}
