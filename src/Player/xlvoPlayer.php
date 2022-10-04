<?php

declare(strict_types=1);

namespace LiveVoting\Player;

use LiveVoting\Cache\CachingActiveRecord;
use LiveVoting\Cache\xlvoCacheFactory;
use LiveVoting\Context\Param\ParamManager;
use LiveVoting\QuestionTypes\xlvoQuestionTypes;
use LiveVoting\Round\xlvoRound;
use LiveVoting\Vote\xlvoVote;
use LiveVoting\Voter\xlvoVoter;
use LiveVoting\Voting\xlvoVoting;
use stdClass;
use xlvoCorrectOrderGUI;

class xlvoPlayer extends CachingActiveRecord
{
    public const STAT_STOPPED = 0;
    public const STAT_RUNNING = 1;
    public const STAT_START_VOTING = 2;
    public const STAT_END_VOTING = 3;
    public const STAT_FROZEN = 4;
    public const SECONDS_ACTIVE = 4;
    public const SECONDS_TO_SLEEP = 30;
    public const CACHE_TTL_SECONDS = 1800;
    public const TABLE_NAME = 'rep_robj_xlvo_player_n';
    protected static array $instance_cache = [];
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
    protected int $obj_id;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $active_voting;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $status;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $frozen = true;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $timestamp_refresh;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $show_results = false;
    /**
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           1024
     */
    protected array $button_states = [];
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           2
     */
    protected int $countdown = 0;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $countdown_start = 0;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $force_reload = false;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $round_id = 0;

    /**
     * @deprecated
     */
    public static function returnDbTableName(): string
    {
        return self::TABLE_NAME;
    }

    public static function getInstanceForObjId(int $obj_id): xlvoPlayer
    {
        //use in memory instance if possible
        if (!empty(self::$instance_cache[$obj_id])) {
            return self::$instance_cache[$obj_id];
        }

        //if possible use cache
        $cache = xlvoCacheFactory::getInstance();
        if ($cache && $cache->isActive()) {
            return self::getInstanceForObjectIdWithCache($obj_id);
        }

        return self::getInstanceForObjectIdWithoutCache($obj_id);
    }

    private static function getInstanceForObjectIdWithCache($obj_id)
    {
        $key = self::TABLE_NAME . '_obj_id_' . $obj_id;
        $cache = xlvoCacheFactory::getInstance();
        $instance = $cache->get($key);

        if ($instance instanceof stdClass) {
            $player = self::find($instance->id); //relay on the ar connector cache

            self::$instance_cache[$obj_id] = $player;

            return self::$instance_cache[$obj_id];
        }

        $obj = self::where(array('obj_id' => $obj_id))->first();
        if (!$obj instanceof self) {
            $obj = new self();
            $obj->setObjId($obj_id);
        } else {
            $player = new stdClass();
            $player->id = $obj->getPrimaryFieldValue();
            $cache->set($key, $player, self::CACHE_TTL_SECONDS);
        }

        self::$instance_cache[$obj_id] = $obj;

        return self::$instance_cache[$obj_id];
    }

    private static function getInstanceForObjectIdWithoutCache(int $obj_id)
    {
        $obj = self::where(array('obj_id' => $obj_id))->first();
        if (!$obj instanceof self) {
            $obj = new self();
            $obj->setObjId($obj_id);
        }
        self::$instance_cache[$obj_id] = $obj;

        return self::$instance_cache[$obj_id];
    }

    public function getConnectorContainerName(): string
    {
        return self::TABLE_NAME;
    }

    public function toggleFreeze(int $voting_id = 0): void
    {
        if ($this->isFrozen()) {
            $this->unfreeze($voting_id);
        } else {
            $this->freeze();
        }
    }

    public function isFrozen(): bool
    {
        return $this->frozen;
    }

    public function setFrozen(bool $frozen): void
    {
        $this->frozen = $frozen;
    }

    public function unfreeze(int $voting_id = 0): void
    {
        if ($voting_id > 0) {
            $this->setActiveVoting($voting_id);
        }

        $this->setFrozen(false);
        $this->resetCountDown(false);
        $this->setButtonStates([]);
        $this->resetCountDown(false);
        $this->setTimestampRefresh(time() + self::SECONDS_TO_SLEEP);
        $this->store();
    }

    public function setActiveVoting(int $active_voting): void
    {
        $this->active_voting = $active_voting;
    }

    public function resetCountDown(bool $store = true): void
    {
        $this->setCountdown(0);
        $this->setCountdownStart(0);
        if ($store) {
            $this->store();
        }
    }

    public function freeze(): void
    {
        $this->setFrozen(true);
        $this->resetCountDown(false);
        $this->setButtonStates([]);
        $this->resetCountDown(false);
        $this->setTimestampRefresh(time() + self::SECONDS_TO_SLEEP);
        $this->store();
    }

    /**
     * @param $seconds
     */
    public function startCountDown(int $seconds): void
    {
        $this->unfreeze((int) trim(filter_input(INPUT_GET, ParamManager::PARAM_VOTING), "/"));
        $this->setCountdown($seconds);
        $this->setCountdownStart(time());
        $this->store();
    }

    public function show(): void
    {
        $this->setShowResults(true);
        $this->store();
    }

    public function hide(): void
    {
        $this->setShowResults(false);
        $this->store();
    }

    public function toggleResults(): void
    {
        $this->setShowResults(!$this->isShowResults());
        $this->store();
    }

    public function isShowResults(): bool
    {
        return $this->show_results;
    }

    public function setShowResults(bool $show_results): void
    {
        $this->show_results = $show_results;
    }

    public function terminate(): void
    {
        $this->setStatus(self::STAT_END_VOTING);
        $this->freeze();
    }

    public function getStdClassForVoter(): stdClass
    {
        $obj = new stdClass();
        $obj->status = $this->getStatus(false);
        $obj->force_reload = false;
        $obj->active_voting_id = $this->getActiveVotingId();
        $obj->countdown = $this->remainingCountDown();
        $obj->has_countdown = $this->isCountDownRunning();
        $obj->countdown_classname = $this->getCountdownClassname();
        $obj->frozen = $this->isFrozen();
        $obj->show_results = $this->isShowResults();
        if ($this->getActiveVotingId() === xlvoQuestionTypes::TYPE_FREE_ORDER) {
            $obj->show_correct_order = (bool) $this->getButtonStates(
            )[xlvoCorrectOrderGUI::BUTTON_TOTTLE_DISPLAY_CORRECT_ORDER];
        } else {
            $obj->show_correct_order = false;
        }

        return $obj;
    }

    public function getStatus(bool $simulate_user = false): int
    {
        /*if ($simulate_user && $this->isFrozenOrUnattended()) {
            return self::STAT_FROZEN;
        }*/

        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getActiveVotingId(): int
    {
        return $this->active_voting;
    }

    public function remainingCountDown(): int
    {
        return $this->getCountdownStart() - time() + $this->getCountdown();
    }

    public function getCountdownStart(): int
    {
        return $this->countdown_start;
    }

    public function setCountdownStart(int $countdown_start): void
    {
        $this->countdown_start = $countdown_start;
    }

    public function getCountdown(): int
    {
        return $this->countdown;
    }

    public function setCountdown(int $countdown): void
    {
        $this->countdown = $countdown;
    }

    public function isCountDownRunning(): bool
    {
        return ($this->remainingCountDown() > 0 || $this->getCountdownStart() > 0);
    }

    public function getCountdownClassname(): string
    {
        $cd = $this->remainingCountDown();

        return $cd > 10 ? 'running' : ($cd > 5 ? 'warning' : 'danger');
    }

    public function getButtonStates(): array
    {
        return $this->button_states;
    }

    public function setButtonStates(array $button_states): void
    {
        $this->button_states = $button_states;
    }

    public function getStdClassForPlayer(): stdClass
    {
        $obj = new stdClass();
        $obj->is_first = $this->getCurrentVotingObject()->isFirst();
        $obj->is_last = $this->getCurrentVotingObject()->isLast();
        $obj->status = $this->getStatus(false);
        $obj->active_voting_id = $this->getActiveVotingId();
        $obj->show_results = $this->isShowResults();
        $obj->frozen = $this->isFrozen();
        $obj->votes = xlvoVote::where([
            'voting_id' => $this->getCurrentVotingObject()->getId(),
            'status' => xlvoVote::STAT_ACTIVE,
            'round_id' => $this->getRoundId()
        ])->count();

        $last_update = xlvoVote::where([
            'voting_id' => $this->getActiveVotingId(),
            'status' => xlvoVote::STAT_ACTIVE,
            'round_id' => $this->getRoundId()
        ])->orderBy('last_update', 'DESC')->getArray('last_update', 'last_update');
        $updates = array_values($last_update);
        $last_update = array_shift($updates);
        $obj->last_update = (int) $last_update;
        $obj->attendees = self::plugin()->translate("start_online", "", [(int) xlvoVoter::countVoters($this->getId())]);
        $obj->qtype = $this->getQuestionTypeClassName();
        $obj->countdown = $this->remainingCountDown();
        $obj->has_countdown = $this->isCountDownRunning();

        return $obj;
    }

    protected function getCurrentVotingObject(): xlvoVoting
    {
        return xlvoVoting::find($this->getActiveVotingId());
    }

    public function getRoundId(): int
    {
        return $this->round_id;
    }

    public function setRoundId(int $round_id): void
    {
        $this->round_id = $round_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getQuestionTypeClassName(): string
    {
        return xlvoQuestionTypes::getClassName($this->getActiveVotingId());
    }

    public function isFrozenOrUnattended(): bool
    {
        if ($this->getStatus(false) === self::STAT_RUNNING) {
            return $this->isFrozen() || $this->isUnattended();
        }

        return false;
    }

    public function isUnattended(): bool
    {
        if ($this->getStatus() !== self::STAT_STOPPED && ($this->getTimestampRefresh() < (time(
        ) - self::SECONDS_TO_SLEEP))) {
            $this->setStatus(self::STAT_STOPPED);
            $this->store();
        }
        if ($this->getStatus() === self::STAT_START_VOTING) {
            return false;
        }
        if ($this->getStatus() === self::STAT_STOPPED) {
            return false;
        }

        return $this->getTimestampRefresh() < (time() - self::SECONDS_ACTIVE);
    }

    public function getTimestampRefresh(): int
    {
        return $this->timestamp_refresh;
    }

    public function setTimestampRefresh(int $timestamp_refresh): void
    {
        $this->timestamp_refresh = $timestamp_refresh;
    }

    public function prepareStart(int $voting_id): void
    {
        $this->setStatus(self::STAT_START_VOTING);
        $this->setActiveVoting($voting_id);
        $this->setRoundId(xlvoRound::getLatestRoundId($this->getObjId()));
        $this->store();
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function setObjId(int $obj_id): void
    {
        $this->obj_id = $obj_id;
    }

    public function attend(): void
    {
        $this->setStatus(self::STAT_RUNNING);
        $this->setTimestampRefresh(time());
        if ($this->remainingCountDown() <= 0 && $this->getCountdownStart() > 0) {
            $this->freeze();
        }
    }

    public function isForceReload(): bool
    {
        return $this->force_reload;
    }

    public function setForceReload(bool $force_reload): void
    {
        $this->force_reload = $force_reload;
    }

    public function sleep(string $field_name): ?string
    {
        switch ($field_name) {
            case 'button_states':
                $var = $this->{$field_name};
                if (!is_array($var)) {
                    $var = [];
                }

                return json_encode($var, JSON_THROW_ON_ERROR);
        }

        return null;
    }

    public function wakeUp(string $field_name, $field_value): ?string
    {
        switch ($field_name) {
            case 'button_states':
                if (!is_string($field_value)) {
                    return null;
                }
                $var = json_decode($field_value, true, 512, JSON_THROW_ON_ERROR);

                //check if we got the database entry
                if (!is_array($var)) {
                    $var = [];
                }

                //check if we got a cache entry
                if (is_array($field_value)) {
                    $var = $field_value;
                }

                return $var;
        }

        return null;
    }
}
