<?php

declare(strict_types=1);

namespace LiveVoting\Voting;

use ActiveRecordList;
use ilException;
use ilLiveVotingPlugin;
use LiveVoting\Context\Param\ParamManager;
use LiveVoting\Exceptions\xlvoPlayerException;
use LiveVoting\Exceptions\xlvoVoterException;
use LiveVoting\Exceptions\xlvoVotingManagerException;
use LiveVoting\Option\xlvoOption;
use LiveVoting\Pin\xlvoPin;
use LiveVoting\Player\xlvoPlayer;
use LiveVoting\QuestionTypes\xlvoInputResultsGUI;
use LiveVoting\QuestionTypes\xlvoQuestionTypes;
use LiveVoting\Round\xlvoRound;
use LiveVoting\User\xlvoUser;
use LiveVoting\Utils\LiveVotingTrait;
use LiveVoting\Vote\xlvoVote;
use srag\DIC\LiveVoting\DICTrait;

class xlvoVotingManager2
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    /** @var xlvoVotingManager2[] */
    protected static array $instances = array();
    protected xlvoPlayer $player;
    protected xlvoVoting $voting;
    protected string $pin = '';
    protected int $obj_id = 0;
    protected ParamManager $param_manager;

    public function __construct($pin)
    {
        if (!empty($pin)) {
            $this->initVoting($pin);
        }
    }

    private function initVoting($pin): void
    {
        $this->setObjId(xlvoPin::checkPinAndGetObjId($pin));
        if ($this->getObjId() === 0) {
            throw new ilException("xlvoVotingManager2: Wrong PIN! - 2");
        }
        $this->setPlayer(xlvoPlayer::getInstanceForObjId($this->getObjId()));

        if ($voting_id = trim(filter_input(INPUT_GET, ParamManager::PARAM_VOTING), "/")) {
            $this->setVoting(xlvoVoting::findOrGetInstance($voting_id));
        } else {
            $this->setVoting(xlvoVoting::findOrGetInstance($this->player->getActiveVotingId()));
        }

        if ($this->getPlayer()->getRoundId() === 0) {
            $this->getPlayer()->setRoundId(xlvoRound::getLatestRoundId($this->getVoting()->getObjId()));
        }
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function setObjId(int $obj_id): void
    {
        $this->obj_id = $obj_id;
    }

    public function getPlayer(): xlvoPlayer
    {
        return $this->player;
    }

    public function setPlayer(xlvoPlayer $player): void
    {
        $this->player = $player;
    }

    public function getVoting(): xlvoVoting
    {
        return $this->voting;
    }

    public function setVoting(xlvoVoting $voting): void
    {
        $this->voting = $voting;
    }

    public static function getInstanceFromObjId(int $obj_id): xlvoVotingManager2
    {
        if (!isset(self::$instances[$obj_id])) {
            /**
             * @var xlvoVotingConfig $xlvoVotingConfig
             */
            $xlvoVotingConfig = xlvoVotingConfig::findOrGetInstance($obj_id);

            self::$instances[$obj_id] = new self($xlvoVotingConfig->getPin());
        }

        return self::$instances[$obj_id];
    }

    /**
     * @throws xlvoVoterException
     */
    public function checkPIN(string $pin): void
    {
        xlvoPin::checkPinAndGetObjId($pin, true);
    }

    /**
     * @param null $option
     */
    public function vote($option = null): void
    {
        $xlvoOption = xlvoOption::findOrGetInstance($option);
        if ($this->hasUserVotedForOption($xlvoOption)) {
            $this->unvote($option);
        } else {
            $vote_id = xlvoVote::vote(
                xlvoUser::getInstance(),
                $this->getVoting()->getId(),
                $this->player->getRoundId(),
                $option
            );
        }
        if (!$this->getVoting()->isMultiSelection()) {
            $this->unvoteAll($vote_id);
        }

        $this->createHistoryObject();
    }

    public function hasUserVotedForOption(xlvoOption $xlvoOption): bool
    {
        $options = [];
        foreach ($this->getVotesOfUser() as $xlvoVote) {
            $options[] = $xlvoVote->getOptionId();
        }

        return in_array($xlvoOption->getId(), $options, true);
    }

    /**
     * @return xlvoVote[]
     */
    public function getVotesOfUser($incl_inactive = false): array
    {
        $xlvoVotes = xlvoVote::getVotesOfUser(
            xlvoUser::getInstance(),
            $this->getVoting()->getId(),
            $this->getPlayer()->getRoundId(),
            $incl_inactive
        );

        return $xlvoVotes;
    }

    public function unvote(int $option = null): void
    {
        xlvoVote::unvote(xlvoUser::getInstance(), $this->getVoting()->getId(), $option);
    }

    public function unvoteAll(int $except_vote_id = null): void
    {
        foreach ($this->getVotesOfUser() as $xlvoVote) {
            if ($except_vote_id && $xlvoVote->getId() === $except_vote_id) {
                continue;
            }
            $xlvoVote->setStatus(xlvoVote::STAT_INACTIVE);
            $xlvoVote->store();
        }
    }

    /**
     * @throws xlvoVotingManagerException
     */
    protected function createHistoryObject(): void
    {
        if ($this->getVotingConfig()->getVotingHistory()) {
            xlvoVote::createHistoryObject(
                xlvoUser::getInstance(),
                $this->getVoting()->getId(),
                $this->player->getRoundId()
            );
        }
    }

    /**
     * @throws xlvoVotingManagerException
     */
    public function getVotingConfig(): xlvoVotingConfig
    {
        /**
         * @var xlvoVotingConfig $xlvoVotingConfig
         */
        $xlvoVotingConfig = xlvoVotingConfig::find($this->obj_id);

        if ($xlvoVotingConfig instanceof xlvoVotingConfig) {
            $xlvoVotingConfig->setSelfVote((bool) $_GET['preview']);
            $xlvoVotingConfig->setKeyboardActive((bool) $_GET['key']);

            return $xlvoVotingConfig;
        }

        throw new xlvoVotingManagerException('Returned object is not an instance of xlvoVotingConfig.');
    }

    public function prepare(): void
    {
        $this->getVoting()->regenerateOptionSorting();
        $this->getPlayer()->setStatus(xlvoPlayer::STAT_RUNNING);
        $this->getPlayer()->freeze();
    }

    /**
     * @throws xlvoVotingManagerException
     */
    public function addInput($input): int
    {
        $options = $this->getOptions();
        $options_value_array = array_values($options);
        $option = array_shift($options_value_array);
        if (!$option instanceof xlvoOption) {
            throw new xlvoVotingManagerException('No Option given');
        }
        $xlvoVote = new xlvoVote();
        $xlvoUser = xlvoUser::getInstance();
        if ($xlvoUser->getType() === xlvoUser::TYPE_ILIAS) {
            $xlvoVote->setUserId($xlvoUser->getIdentifier());
            $xlvoVote->setUserIdType(xlvoVote::USER_ILIAS);
        } else {
            $xlvoVote->setUserIdentifier($xlvoUser->getIdentifier());
            $xlvoVote->setUserIdType(xlvoVote::USER_ANONYMOUS);
        }
        $xlvoVote->setVotingId($this->getVoting()->getId());
        $xlvoVote->setOptionId($option->getId());
        $xlvoVote->setType(xlvoQuestionTypes::TYPE_FREE_INPUT);
        $xlvoVote->setStatus(xlvoVote::STAT_ACTIVE);
        $xlvoVote->setFreeInput($input);
        $xlvoVote->setRoundId(xlvoRound::getLatestRoundId($this->obj_id));
        $xlvoVote->create();

        return $xlvoVote->getId();
    }

    /**
     * @return xlvoOption[]
     */
    public function getOptions(): array
    {
        return $this->voting->getVotingOptions();
    }

    public function countVotesOfOption(int $option_id): int
    {
        return xlvoVote::where([
            'option_id' => $option_id,
            'status' => xlvoVote::STAT_ACTIVE,
            'round_id' => $this->player->getRoundId(),
        ])->count();
    }

    /**
     * @return xlvoVote[]
     */
    public function getVotesOfOption(int $option_id): array
    {
        /**
         * @var xlvoVote[] $xlvoVotes
         */
        return xlvoVote::where([
            'option_id' => $option_id,
            'status' => xlvoVote::STAT_ACTIVE,
            'round_id' => $this->player->getRoundId(),
        ])->get();
    }

    public function getFirstVoteOfUser(bool $incl_inactive = false): xlvoVote
    {
        $xlvoVotes = $this->getVotesOfUser($incl_inactive);
        $array = array_values($xlvoVotes);
        $xlvoVote = array_shift($array);

        return ($xlvoVote instanceof xlvoVote) ? $xlvoVote : new xlvoVote();
    }

    public function getVotesOfUserOfOption(int $option_id): array
    {
        $return = [];
        foreach ($this->getVotesOfUser() as $xlvoVote) {
            if ($xlvoVote->getOptionId() === $option_id) {
                $return[] = $xlvoVote;
            }
        }

        return $return;
    }

    public function open(int $voting_id): void
    {
        if ($this->getVotingsList()->where(['id' => $voting_id])->hasSets()) {
            $this->player->setActiveVoting($voting_id);
            $this->player->store();
        }
    }

    protected function getVotingsList(string $order = 'ASC'): ActiveRecordList
    {
        return xlvoVoting::where([
            'obj_id' => $this->getObjId(),
            'voting_status' => xlvoVoting::STAT_ACTIVE,
        ])->where(['voting_type' => xlvoQuestionTypes::getActiveTypes()])->orderBy('position', $order);
    }

    public function previous(): void
    {
        if ($this->getVoting()->isFirst()) {
            return;
        }
        $prev_id = $this->getVotingsList('DESC')->where(array('position' => $this->voting->getPosition()), '<')->limit(
            0,
            1
        )->getArray('id', 'id');
        $array = array_values($prev_id);
        $prev_id = array_shift($array);
        $this->handleQuestionSwitching();

        $this->player->setActiveVoting($prev_id);
        $this->player->store();
        $this->getVoting()->regenerateOptionSorting();
    }

    /**
     * @throws xlvoVotingManagerException
     */
    public function handleQuestionSwitching(): void
    {
        switch ($this->getVotingConfig()->getResultsBehaviour()) {
            case xlvoVotingConfig::B_RESULTS_ALWAY_ON:
                $this->player->setShowResults(true);
                break;
            case xlvoVotingConfig::B_RESULTS_ALWAY_OFF:
                $this->player->setShowResults(false);
                break;
            case xlvoVotingConfig::B_RESULTS_REUSE:
                $this->player->setShowResults($this->player->isShowResults());
                break;
        }

        switch ($this->getVotingConfig()->getFrozenBehaviour()) {
            case xlvoVotingConfig::B_FROZEN_ALWAY_ON:
                $this->player->setFrozen(false);
                break;
            case xlvoVotingConfig::B_FROZEN_ALWAY_OFF:
                $this->player->setFrozen(true);
                break;
            case xlvoVotingConfig::B_FROZEN_REUSE:
                $this->player->setFrozen($this->player->isFrozen());
                break;
        }
    }

    public function next(): void
    {
        if ($this->getVoting()->isLast()) {
            return;
        }
        $next_id = $this->getVotingsList()->where(array('position' => $this->voting->getPosition()), '>')->limit(
            0,
            1
        )->getArray('id', 'id');
        $array = array_values($next_id);
        $next_id = array_shift($array);
        $this->handleQuestionSwitching();
        $this->player->setActiveVoting($next_id);
        $this->player->store();
        $this->getVoting()->regenerateOptionSorting();
    }

    public function terminate(): void
    {
        $this->player->terminate();
    }

    public function countdown(int $seconds): void
    {
        $this->player->startCountDown($seconds);
    }

    public function attend(): void
    {
        $this->getPlayer()->attend();
    }

    public function countVotings(): int
    {
        return $this->getVotingsList('ASC')->count();
    }

    public function getVotingPosition(): int
    {
        $voting_position = 1;
        foreach ($this->getVotingsList('ASC')->getArray() as $key => $voting) {
            if ($this->getVoting()->getId() === $key) {
                break;
            }
            $voting_position++;
        }

        return $voting_position;
    }

    public function countVoters(): int
    {
        $q = 'SELECT user_id_type, user_identifier, user_id FROM ' . xlvoVote::TABLE_NAME
            . ' WHERE voting_id = %s AND status = %s AND round_id = %s GROUP BY user_id_type, user_identifier, user_id';

        $res = self::dic()->database()->queryF($q, array('integer', 'integer', 'integer'), array(
            $this->getVoting()->getId(),
            xlvoVote::STAT_ACTIVE,
            $this->player->getRoundId(),
        ));

        return $res->numRows();
    }

    public function getMaxCountOfVotes(): int
    {
        $q = "SELECT MAX(counted) AS maxcount FROM
				( SELECT COUNT(*) AS counted FROM " . xlvoVote::TABLE_NAME . " WHERE voting_id = %s AND status = %s AND round_id = %s GROUP BY option_id )
				AS counts";
        $res = self::dic()->database()->queryF($q, array('integer', 'integer', 'integer'), array(
            $this->getVoting()->getId(),
            xlvoVote::STAT_ACTIVE,
            $this->player->getRoundId(),
        ));
        $data = self::dic()->database()->fetchObject($res);

        return $data->maxcount ?: 0;
    }

    public function hasVotes(): bool
    {
        return ($this->countVotes() > 0);
    }

    public function countVotes(): int
    {
        return xlvoVote::where([
            'voting_id' => $this->getVoting()->getId(),
            'round_id' => $this->getPlayer()->getRoundId(),
            'status' => xlvoVote::STAT_ACTIVE,
        ])->count();
    }

    public function reset(): void
    {
        $this->player->setButtonStates([]);
        $this->player->store();

        xlvoInputResultsGUI::getInstance($this)->reset();
    }

    /**
     * @throws xlvoPlayerException
     */
    public function prepareStart(): bool
    {
        if (!$this->getVotingConfig()->isObjOnline()) {
            throw new xlvoPlayerException('', xlvoPlayerException::OBJ_OFFLINE);
        }

        if ($this->canBeStarted()) {
            $xlvoVoting = $this->getVotingsList()->first();
            $this->getPlayer()->prepareStart($xlvoVoting->getId());

            return true;
        }

        throw new xlvoPlayerException('', xlvoPlayerException::NO_VOTINGS);
    }

    public function canBeStarted(): bool
    {
        return $this->getVotingsList()->hasSets();
    }

    /**
     * @return xlvoVote[]
     */
    public function getVotesOfVoting(bool $order_by_free_input = false): array
    {
        /**
         * @var xlvoVote[] $xlvoVotes
         */
        $where = xlvoVote::where(array(
            'voting_id' => $this->getVoting()->getId(),
            'status' => xlvoOption::STAT_ACTIVE,
            'round_id' => $this->player->getRoundId(),
        ));

        if ($order_by_free_input) {
            $where = $where->orderBy("free_input", "asc");
        }

        return $where->get();
    }

    /**
     * @return xlvoVoting[]
     */
    public function getAllVotings(): array
    {
        return $this->getVotingsList()->get();
    }

    public function getFirstVoteOfUserOfOption($option_id): ?xlvoVote
    {
        foreach ($this->getVotesOfUser() as $xlvoVote) {
            if ($xlvoVote->getOptionId() === $option_id) {
                return $xlvoVote;
            }
        }

        return null;
    }

    public function countOptions(): int
    {
        return count($this->getOptions());
    }

    /**
     * @throws xlvoVotingManagerException
     */
    public function inputOne($array): void
    {
        $this->inputAll([$array]);
    }

    /**
     * @param array $array ... => (input, vote_id)
     * @throws xlvoVotingManagerException
     */
    public function inputAll(array $array): void
    {
        foreach ($array as $item) {
            $this->input($item['input'], $item['vote_id']);
        }
        $this->createHistoryObject();
    }

    /**
     * @throws xlvoVotingManagerException
     */
    protected function input($input, $vote_id): void
    {
        $options = $this->getOptions();
        $array = array_values($options);
        $option = array_shift($array);
        if (!$option instanceof xlvoOption) {
            throw new xlvoVotingManagerException('No Option given');
        }
        /**
         * @var xlvoVote $xlvoVote
         */
        $xlvoVote = xlvoVote::find($vote_id);
        if (!$xlvoVote instanceof xlvoVote) {
            $xlvoVote = new xlvoVote();
        }
        $xlvoUser = xlvoUser::getInstance();
        if ($xlvoUser->getType() === xlvoUser::TYPE_ILIAS) {
            $xlvoVote->setUserId($xlvoUser->getIdentifier());
            $xlvoVote->setUserIdType(xlvoVote::USER_ILIAS);
        } else {
            $xlvoVote->setUserIdentifier($xlvoUser->getIdentifier());
            $xlvoVote->setUserIdType(xlvoVote::USER_ANONYMOUS);
        }
        $xlvoVote->setVotingId($this->getVoting()->getId());
        $xlvoVote->setOptionId($option->getId());
        $xlvoVote->setType(xlvoQuestionTypes::TYPE_FREE_INPUT);
        $xlvoVote->setStatus(xlvoVote::STAT_ACTIVE);
        $xlvoVote->setFreeInput($input);
        $xlvoVote->setRoundId(xlvoRound::getLatestRoundId($this->obj_id));
        $xlvoVote->store();
        if (!$this->getVoting()->isMultiFreeInput()) {
            $this->unvoteAll($xlvoVote->getId());
        }
    }
}
