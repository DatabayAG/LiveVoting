<?php

declare(strict_types=1);

namespace LiveVoting\Vote;

use LiveVoting\Cache\CachingActiveRecord;
use LiveVoting\Option\xlvoOption;
use LiveVoting\QuestionTypes\xlvoResultGUI;
use LiveVoting\User\xlvoUser;
use LiveVoting\User\xlvoVoteHistoryObject;
use LiveVoting\Voting\xlvoVoting;

class xlvoVote extends CachingActiveRecord
{
    public const STAT_INACTIVE = 0;
    public const STAT_ACTIVE = 1;
    public const USER_ILIAS = 0;
    public const USER_ANONYMOUS = 1;
    public const TABLE_NAME = 'rep_robj_xlvo_vote_n';
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     * @db_is_primary       true
     * @con_sequence        true
     */
    protected int $id;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $type;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $status;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $option_id;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $voting_id;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $user_id_type;
    /**
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           256
     */
    protected string $user_identifier = '';
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $user_id = 0;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $last_update;
    /**
     * @var int
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $round_id = 0;
    /**
     * @db_has_field true
     * @db_fieldtype text
     * @db_length    2000
     */
    protected string $free_input;
    /**
     * @db_has_field true
     * @db_fieldtype integer
     * @db_length    8
     */
    protected int $free_input_category;

    /**
     * @deprecated
     */
    public static function returnDbTableName(): string
    {
        return self::TABLE_NAME;
    }

    public static function vote(xlvoUser $xlvoUser, int $voting_id, int $round_id, int $option_id = null): int
    {
        $obj = self::getUserInstance($xlvoUser, $voting_id, $option_id);
        $obj->setStatus(self::STAT_ACTIVE);
        $obj->setRoundId($round_id);
        $obj->store();

        return $obj->getId();
    }

    protected static function getUserInstance(xlvoUser $xlvoUser, int $voting_id, int $option_id): self
    {
        $where = ['voting_id' => $voting_id];
        if ($option_id) {
            $where = ['option_id' => $option_id];
        }
        if ($xlvoUser->isILIASUser()) {
            $where['user_id'] = $xlvoUser->getIdentifier();
        } else {
            $where['user_identifier'] = $xlvoUser->getIdentifier();
        }

        $vote = self::where($where)->first();

        if (!$vote instanceof self) {
            $vote = new self();
        }

        $vote->setUserIdType($xlvoUser->getType());
        if ($xlvoUser->isILIASUser()) {
            $vote->setUserId($xlvoUser->getIdentifier());
        } else {
            $vote->setUserIdentifier($xlvoUser->getIdentifier());
        }
        $vote->setOptionId($option_id);
        $vote->setVotingId($voting_id);

        return $vote;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public static function unvote(xlvoUser $xlvoUser, int $voting_id, int $option_id = null): int
    {
        $obj = self::getUserInstance($xlvoUser, $voting_id, $option_id);
        $obj->setStatus(self::STAT_INACTIVE);
        $obj->store();

        return $obj->getId();
    }

    /**
     * @return xlvoVote[]
     */
    public static function getVotesOfUser(xlvoUser $xlvoUser, int $voting_id, int $round_id, bool $incl_inactive = false): array
    {
        $where = [
            'voting_id' => $voting_id,
            'status' => self::STAT_ACTIVE,
            'round_id' => $round_id,
        ];
        if ($incl_inactive) {
            $where['status'] = [
                self::STAT_INACTIVE,
                self::STAT_ACTIVE,
            ];
        }
        if ($xlvoUser->isILIASUser()) {
            $where['user_id'] = $xlvoUser->getIdentifier();
        } else {
            $where['user_identifier'] = $xlvoUser->getIdentifier();
        }

        return self::where($where)->get();
    }

    public static function createHistoryObject(xlvoUser $xlvoUser, int $voting_id, int $round_id): void
    {
        $historyObject = new xlvoVoteHistoryObject();

        if ($xlvoUser->isILIASUser()) {
            $historyObject->setUserIdType(self::USER_ILIAS);
            $historyObject->setUserId($xlvoUser->getIdentifier());
            $historyObject->setUserIdentifier(null);
        } else {
            $historyObject->setUserIdType(self::USER_ANONYMOUS);
            $historyObject->setUserId(null);
            $historyObject->setUserIdentifier($xlvoUser->getIdentifier());
        }

        $historyObject->setVotingId($voting_id);
        $historyObject->setRoundId($round_id);
        $historyObject->setTimestamp(time());
        $gui = xlvoResultGUI::getInstance(xlvoVoting::find($voting_id));

        $votes = self::where([
            'voting_id' => $voting_id,
            'status' => xlvoOption::STAT_ACTIVE,
            'round_id' => $round_id,
        ]);
        if ($xlvoUser->isILIASUser()) {
            $votes->where(["user_id" => $xlvoUser->getIdentifier()]);
        } else {
            $votes->where(["user_identifier" => $xlvoUser->getIdentifier()]);
        }
        $votes = $votes->get();
        $historyObject->setAnswer($gui->getTextRepresentation($votes));

        $historyObject->store();
    }

    public function getConnectorContainerName(): string
    {
        return self::TABLE_NAME;
    }

    public function isActive(): bool
    {
        return ($this->getStatus() === self::STAT_ACTIVE);
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getOption(): xlvoOption
    {
        return xlvoOption::find($this->getOptionId());
    }

    public function getOptionId(): int
    {
        return $this->option_id;
    }

    public function setOptionId(int $option_id): void
    {
        $this->option_id = $option_id;
    }

    public function update(): void
    {
        $this->setLastUpdate(time());
        parent::update();
    }

    public function create(): void
    {
        $this->setLastUpdate(time());
        parent::create();
    }

    public function sleep($field_name)
    {
        switch ($field_name) {
            case 'free_input':
                return preg_replace('/[\x{10000}-\x{10FFFF}]/u', "", $this->free_input);
        }

        return parent::sleep($field_name);
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getVotingId(): int
    {
        return $this->voting_id;
    }

    public function setVotingId(int $voting_id): void
    {
        $this->voting_id = $voting_id;
    }

    public function getUserIdType(): int
    {
        return $this->user_id_type;
    }

    public function setUserIdType(int $user_id_type): void
    {
        $this->user_id_type = $user_id_type;
    }

    public function getUserIdentifier(): string
    {
        return $this->user_identifier;
    }

    public function setUserIdentifier(string $user_identifier): void
    {
        $this->user_identifier = $user_identifier;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getLastUpdate(): int
    {
        return $this->last_update;
    }

    public function setLastUpdate(int $last_update): void
    {
        $this->last_update = $last_update;
    }

    public function getRoundId(): int
    {
        return $this->round_id;
    }

    public function setRoundId(int $round_id): void
    {
        $this->round_id = $round_id;
    }

    public function getFreeInput(): string
    {
        return $this->free_input;
    }

    public function setFreeInput(string $free_input): void
    {
        $this->free_input = $free_input;
    }

    public function getFreeInputCategory(): int
    {
        return $this->free_input_category;
    }

    public function setFreeInputCategory(int $free_input_category): void
    {
        $this->free_input_category = $free_input_category;
    }
}
