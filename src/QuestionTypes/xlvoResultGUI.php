<?php

declare(strict_types=1);

namespace LiveVoting\QuestionTypes;

use ilException;
use ilLiveVotingPlugin;
use LiveVoting\Option\xlvoOption;
use LiveVoting\QuestionTypes\CorrectOrder\xlvoCorrectOrderResultGUI;
use LiveVoting\QuestionTypes\FreeInput\xlvoFreeInputResultGUI;
use LiveVoting\QuestionTypes\FreeOrder\xlvoFreeOrderResultGUI;
use LiveVoting\QuestionTypes\NumberRange\xlvoNumberRangeResultGUI;
use LiveVoting\QuestionTypes\SingleVote\xlvoSingleVoteResultGUI;
use LiveVoting\Utils\LiveVotingTrait;
use LiveVoting\Vote\xlvoVote;
use LiveVoting\Voting\xlvoVoting;
use srag\DIC\LiveVoting\DICTrait;

abstract class xlvoResultGUI
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    protected xlvoVoting $voting;
    /** @var xlvoOption[] */
    protected array $options;

    public function __construct(xlvoVoting $voting)
    {
        $this->voting = $voting;
        $this->options = $voting->getVotingOptions();
    }

    /**
     * @throws ilException         Throws an ilException if no result gui class was found for the
     *                              given voting type.
     */
    public static function getInstance(xlvoVoting $voting): self
    {
        $class = xlvoQuestionTypes::getClassName((int) $voting->getVotingType());

        switch ($class) {
            case xlvoQuestionTypes::CORRECT_ORDER:
                return new xlvoCorrectOrderResultGUI($voting);
            case xlvoQuestionTypes::FREE_INPUT:
                return new xlvoFreeInputResultGUI($voting);
            case xlvoQuestionTypes::FREE_ORDER:
                return new xlvoFreeOrderResultGUI($voting);
            case xlvoQuestionTypes::SINGLE_VOTE:
                return new xlvoSingleVoteResultGUI($voting);
            case xlvoQuestionTypes::NUMBER_RANGE:
                return new xlvoNumberRangeResultGUI($voting);
            default:
                throw new ilException('Could not find the result gui for the given voting.');
        }
    }

    /**
     * @param xlvoVote[] $votes
     */
    abstract public function getTextRepresentation(array $votes): string;

    /**
     * @param xlvoVote[] $votes
     */
    abstract public function getAPIRepresentation(array $votes): string;
}
