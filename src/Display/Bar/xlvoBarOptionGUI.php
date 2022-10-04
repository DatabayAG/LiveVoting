<?php

declare(strict_types=1);

namespace LiveVoting\Display\Bar;

use ilLiveVotingPlugin;
use ilTemplate;
use LiveVoting\Option\xlvoOption;
use LiveVoting\Vote\xlvoVote;
use LiveVoting\Voting\xlvoVoting;
use LiveVoting\Voting\xlvoVotingManager2;
use srag\DIC\LiveVoting\DICTrait;

class xlvoBarOptionGUI implements xlvoGeneralBarGUI
{
    use DICTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    protected xlvoVoting $voting;
    protected xlvoOption $option;
    protected string $option_letter;
    protected ilTemplate $tpl;
    protected xlvoVotingManager2 $voting_manager;

    public function __construct(xlvoVoting $voting, xlvoOption $option, string $option_letter)
    {
        $this->voting_manager = xlvoVotingManager2::getInstanceFromObjId($voting->getObjId());
        $this->voting = $voting;
        $this->option = $option;
        $this->option_letter = $option_letter;
        $this->tpl = self::plugin()->template('default/Display/Bar/tpl.bar_option.html');
    }

    public function getHTML(): string
    {
        $this->render();

        return $this->tpl->get();
    }

    protected function render(): void
    {
        $this->tpl->setVariable('OPTION_LETTER', $this->option_letter);
        $this->tpl->setVariable('OPTION_ID', $this->option->getId());
        $this->tpl->setVariable('TITLE', $this->option->getTextForPresentation());
        $this->tpl->setVariable('OPTION_ACTIVE', $this->getActiveBar());
        $this->tpl->setVariable('VOTE_ID', $this->getVoteId());
    }

    private function getActiveBar(): string
    {
        /**
         * @var xlvoVote $vote
         */
        $vote = $this->voting_manager->getVotesOfUserOfOption($this->voting->getId(), $this->option->getId())->first(
        ); // TODO: Invalid method call?
        if ($vote instanceof xlvoVote) {
            if ($vote->getStatus() === 1) {
                return "active";
            }

            return "";
        }

        return "";
    }

    /**
     * @return int|string
     */
    private function getVoteId()
    {
        /**
         * @var xlvoVote $vote
         */
        $vote = $this->voting_manager->getVotesOfUserOfOption($this->voting->getId(), $this->option->getId())->first(
        ); // TODO: Invalid method call?
        if ($vote instanceof xlvoVote) {
            return $vote->getId();
        }

        return 0;
    }
}
