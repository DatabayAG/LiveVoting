<?php

declare(strict_types=1);

namespace LiveVoting\QuestionTypes\NumberRange;

use LiveVoting\QuestionTypes\xlvoResultGUI;
use LiveVoting\Vote\xlvoVote;

class xlvoNumberRangeResultGUI extends xlvoResultGUI
{
    /**
     * @param xlvoVote[] $votes
     */
    public function getTextRepresentation(array $votes): string
    {
        return $this->createCSV($votes);
    }

    private function createCSV(array $votes): string
    {
        $testVotes = [];

        foreach ($votes as $vote) {
            $percentage = $this->voting->getPercentage() === 1 ? ' %' : '';
            $testVotes[] = "{$vote->getFreeInput()}{$percentage}";
        }

        return implode(', ', $testVotes);
    }

    /**
     * @param xlvoVote[] $votes
     */
    public function getAPIRepresentation(array $votes): string
    {
        return $this->createCSV($votes);
    }
}
