<?php

declare(strict_types=1);

namespace LiveVoting\QuestionTypes\SingleVote;

use LiveVoting\Display\Bar\xlvoBarCollectionGUI;
use LiveVoting\Display\Bar\xlvoBarPercentageGUI;
use LiveVoting\QuestionTypes\xlvoInputResultsGUI;
use LiveVoting\Vote\xlvoVote;
use xlvoSingleVoteGUI;

class xlvoSingleVoteResultsGUI extends xlvoInputResultsGUI
{
    public function getHTML(): string
    {
        if ($this->voting->isMultiSelection()) {
            return $this->getHTMLMulti();
        }

        return $this->getHTMLSingle();
    }

    protected function getHTMLMulti(): string
    {
        $total_votes = $this->manager->countVotes();
        $voters = $this->manager->countVoters();

        $bars = new xlvoBarCollectionGUI();
        $bars->setShowTotalVoters(false);
        $bars->setTotalVoters($voters);
        $bars->setShowTotalVotes($this->voting->isMultiSelection());
        $bars->setTotalVotes($total_votes);

        foreach ($this->voting->getVotingOptions() as $xlvoOption) {
            $xlvoBarPercentageGUI = new xlvoBarPercentageGUI();
            $xlvoBarPercentageGUI->setOptionLetter($xlvoOption->getCipher());
            $xlvoBarPercentageGUI->setTitle($xlvoOption->getTextForPresentation());
            $xlvoBarPercentageGUI->setVotes($this->manager->countVotesOfOption($xlvoOption->getId()));
            $xlvoBarPercentageGUI->setMaxVotes($voters);
            $xlvoBarPercentageGUI->setShowInPercent(!$this->isShowAbsolute());
            $bars->addBar($xlvoBarPercentageGUI);
        }

        return $bars->getHTML();
    }

    protected function isShowAbsolute(): bool
    {
        $states = $this->getButtonsStates();

        return ($this->manager->getPlayer()->isShowResults(
        ) && $states[xlvoSingleVoteGUI::BUTTON_TOGGLE_PERCENTAGE]);
    }

    protected function getButtonsStates(): array
    {
        return $this->manager->getPlayer()->getButtonStates();
    }

    protected function getHTMLSingle(): string
    {
        $total_votes = $this->manager->countVotes();
        $voters = $this->manager->countVoters();

        $bars = new xlvoBarCollectionGUI();
        $bars->setShowTotalVoters(false);
        $bars->setTotalVoters($voters);
        $bars->setShowTotalVotes(true);
        $bars->setTotalVotes($voters);

        foreach ($this->voting->getVotingOptions() as $xlvoOption) {
            $xlvoBarPercentageGUI = new xlvoBarPercentageGUI();
            $xlvoBarPercentageGUI->setOptionLetter($xlvoOption->getCipher());
            $xlvoBarPercentageGUI->setTitle($xlvoOption->getTextForPresentation());
            $xlvoBarPercentageGUI->setVotes($this->manager->countVotesOfOption($xlvoOption->getId()));
            $xlvoBarPercentageGUI->setMaxVotes($total_votes);
            $xlvoBarPercentageGUI->setShowInPercent(!$this->isShowAbsolute());
            $bars->addBar($xlvoBarPercentageGUI);
        }

        return $bars->getHTML();
    }

    /**
     * @param xlvoVote[] $votes
     */
    public function getTextRepresentationForVotes(array $votes): string
    {
        return "TODO"; //TODO: implement me.
    }
}
