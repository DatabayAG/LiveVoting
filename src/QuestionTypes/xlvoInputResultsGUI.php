<?php

declare(strict_types=1);

namespace LiveVoting\QuestionTypes;

use ilException;
use ilLiveVotingPlugin;
use LiveVoting\QuestionTypes\CorrectOrder\xlvoCorrectOrderResultsGUI;
use LiveVoting\QuestionTypes\FreeInput\xlvoFreeInputResultsGUI;
use LiveVoting\QuestionTypes\FreeOrder\xlvoFreeOrderResultsGUI;
use LiveVoting\QuestionTypes\NumberRange\xlvoNumberRangeResultsGUI;
use LiveVoting\QuestionTypes\SingleVote\xlvoSingleVoteResultsGUI;
use LiveVoting\Utils\LiveVotingTrait;
use LiveVoting\Vote\xlvoVote;
use LiveVoting\Voting\xlvoVoting;
use LiveVoting\Voting\xlvoVotingManager2;
use srag\DIC\LiveVoting\DICTrait;

abstract class xlvoInputResultsGUI
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    protected xlvoVoting $voting;
    protected xlvoVotingManager2 $manager;

    public function __construct(xlvoVotingManager2 $manager, xlvoVoting $voting)
    {
        $this->manager = $manager;
        $this->voting = $voting;
    }

    public static function addJsAndCss(): void
    {
    }

    /**
     * @throws ilException         Throws an ilException if no results gui class was found.
     */
    public static function getInstance(xlvoVotingManager2 $manager)
    {
        $class = xlvoQuestionTypes::getClassName($manager->getVoting()->getVotingType());
        switch ($class) {
            case xlvoQuestionTypes::CORRECT_ORDER:
                return new xlvoCorrectOrderResultsGUI($manager, $manager->getVoting());
            case xlvoQuestionTypes::FREE_INPUT:
                return new xlvoFreeInputResultsGUI($manager, $manager->getVoting());
            case xlvoQuestionTypes::FREE_ORDER:
                return new xlvoFreeOrderResultsGUI($manager, $manager->getVoting());
            case xlvoQuestionTypes::SINGLE_VOTE:
                return new xlvoSingleVoteResultsGUI($manager, $manager->getVoting());
            case xlvoQuestionTypes::NUMBER_RANGE:
                return new xlvoNumberRangeResultsGUI($manager, $manager->getVoting());
            default:
                throw new ilException('Could not find the results gui for the given voting.');
        }
    }

    protected function txt(string $key): string
    {
        return self::plugin()->translate($this->manager->getVoting()->getVotingType() . '_' . $key, 'qtype');
    }

    public function reset(): void
    {
        /**
         * @var xlvoVote $xlvoVote
         */
        foreach (
            xlvoVote::where(
                [
                    'voting_id' => $this->manager->getVoting()->getId(),
                    'round_id' => $this->manager->getPlayer()->getRoundId()
                ]
            )
                    ->get() as $xlvoVote
        ) {
            $xlvoVote->delete();
        }
    }

    abstract public function getHTML(): string;

    /**
     * @param xlvoVote[] $votes
     */
    abstract public function getTextRepresentationForVotes(array $votes): string;
}
