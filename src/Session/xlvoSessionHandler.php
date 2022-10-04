<?php

declare(strict_types=1);

namespace LiveVoting\Session;

use ilLiveVotingPlugin;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

class xlvoSessionHandler
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;

    public function open(string $save_path, string $sessionid): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $sessionid): string
    {
        return '';
    }

    public function write(string $sessionid, string $sessiondata): bool
    {
        return true;
    }

    public function destroy(int $sessionid): bool
    {
        return true;
    }

    public function gc(int $maxlifetime): bool
    {
        return true;
    }
}
