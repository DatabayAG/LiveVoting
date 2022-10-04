<?php

declare(strict_types=1);

namespace LiveVoting\Results;

use Closure;
use ilLiveVotingPlugin;
use LiveVoting\QuestionTypes\xlvoResultGUI;
use LiveVoting\User\xlvoParticipant;
use LiveVoting\User\xlvoParticipants;
use LiveVoting\Utils\LiveVotingTrait;
use LiveVoting\Vote\xlvoVote;
use LiveVoting\Voting\xlvoVoting;
use srag\DIC\LiveVoting\DICTrait;

class xlvoResults
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    protected int $obj_id = 0;
    protected int $round_id = 0;

    public function __construct(int $obj_id, int $round_id)
    {
        $this->obj_id = $obj_id;
        $this->round_id = $round_id;
    }

    /**
     * @throws \Exception
     */
    public function getData(
        array $filter = null,
        callable $formatParticipantCallable = null,
        callable $concatVotesCallable = null
    ): array {
        if (!$formatParticipantCallable) {
            $formatParticipantCallable = $this->getFormatParticipantCallable();
        }

        if (!$concatVotesCallable) {
            $concatVotesCallable = $this->getConcatVotesCallable();
        }

        $obj_id = $this->getObjId();
        $votingRecords = xlvoVoting::where(["obj_id" => $obj_id]);
        if ($filter['voting']) {
            $votingRecords->where(["id" => $filter['voting']]);
        }
        if ($filter['voting_title']) {
            $votingRecords->where(["id" => $filter['voting_title']]);
        }
        /**
         * @var xlvoVoting[]      $votings
         * @var xlvoVoting        $voting
         * @var xlvoParticipant[] $participants
         */
        $votings = $votingRecords->get();
        $round_id = $this->getRoundId();
        $participants = xlvoParticipants::getInstance($obj_id)->getParticipantsForRound(
            $round_id,
            $filter['participant']
        );
        $data = array();
        foreach ($participants as $participant) {
            foreach ($votings as $voting) {
                $votes = xlvoVote::where(array(
                    "round_id" => $round_id,
                    "voting_id" => $voting->getId(),
                    "user_id" => $participant->getUserId(),
                    "user_identifier" => $participant->getUserIdentifier(),
                    "status" => xlvoVote::STAT_ACTIVE,
                ))->get();
                $vote_array_values = array_values($votes);
                $vote = array_shift($vote_array_values);
                $vote_ids = array_keys($votes);
                $data[] = [
                    "position" => (int) $voting->getPosition(),
                    "participant" => $formatParticipantCallable($participant),
                    "user_id" => $participant->getUserId(),
                    "user_identifier" => $participant->getUserIdentifier(),
                    "title" => $voting->getTitle(),
                    "question" => $voting->getRawQuestion(),
                    "answer" => $concatVotesCallable($voting, $votes),
                    "answer_ids" => $vote_ids,
                    "voting_id" => $voting->getId(),
                    "round_id" => $round_id,
                    "id" => ($vote instanceof xlvoVote ? $vote->getId() : ''),
                ];
            }
        }

        return $data;
    }

    protected function getFormatParticipantCallable(): Closure
    {
        return static function (xlvoParticipant $participant) {
            return $participant->getUserIdentifier();
        };
    }

    protected function getConcatVotesCallable(): Closure
    {
        return static function (xlvoVoting $voting, array $votes) {
            return xlvoResultGUI::getInstance($voting)->getAPIRepresentation($votes);
        };
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function setObjId(int $obj_id): void
    {
        $this->obj_id = $obj_id;
    }

    public function getRoundId(): int
    {
        return $this->round_id;
    }

    public function setRoundId(int $round_id): void
    {
        $this->round_id = $round_id;
    }
}
