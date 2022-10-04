<?php

declare(strict_types=1);

namespace LiveVoting\User;

use ilLiveVotingPlugin;
use LiveVoting\Utils\LiveVotingTrait;
use LiveVoting\Vote\xlvoVote;
use srag\DIC\LiveVoting\DICTrait;

class xlvoParticipants
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    /** @var xlvoParticipants[] */
    protected static array $instances = [];
    protected int $obj_id;

    protected function __construct(int $obj_id)
    {
        $this->obj_id = $obj_id;
    }

    public static function getInstance(int $obj_id): xlvoParticipants
    {
        if (!self::$instances[$obj_id]) {
            self::$instances[$obj_id] = new xlvoParticipants($obj_id);
        }

        return self::$instances[$obj_id];
    }

    /**
     * @return xlvoParticipant[]
     */
    public function getParticipantsForRound(int $round_id, string $filter = null): array
    {
        if ($filter) {
            $query = "SELECT DISTINCT user_identifier, user_id FROM " . xlvoVote::TABLE_NAME
                . " WHERE round_id = %s AND (user_identifier LIKE %s OR user_id = %s)";
            $result = self::dic()->database()->queryF(
                $query,
                array("integer", "text", "integer"),
                array($round_id, $filter, $filter)
            );
        } else {
            $query = "SELECT DISTINCT user_identifier, user_id FROM " . xlvoVote::TABLE_NAME . " WHERE round_id = %s";
            $result = self::dic()->database()->queryF($query, array("integer"), array($round_id));
        }

        $rows = array();
        $i = 0;
        while ($row = self::dic()->database()->fetchAssoc($result)) {
            $i++;
            if ($filter && $row['user_id'] !== $filter && $row['user_identifier'] !== $filter) {
                continue;
            }
            $user = new xlvoParticipant();
            $user->setNumber($i);
            $user->setUserId($row['user_id']);
            $user->setUserIdentifier($row['user_identifier']);
            $user->setUserIdType($row['user_id'] ? xlvoUser::TYPE_ILIAS : xlvoUser::TYPE_PIN);
            $rows[] = $user;
        }

        return $rows;
    }
}
