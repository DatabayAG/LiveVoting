<?php

declare(strict_types=1);

namespace LiveVoting\Context;

use ilLiveVotingPlugin;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

class xlvoRbacReview
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;

    public function assignedUsers(int $a_rol_id): array
    {
        return [];
    }

    public function assignedGlobalRoles(int $user_id): array
    {
        return [];
    }

    public function assignedRoles(int $a_usr_id): array
    {
        return [];
    }

    public function isAssignedToAtLeastOneGivenRole(int $a_usr_id, array $a_role_ids): bool
    {
        return false;
    }
}
