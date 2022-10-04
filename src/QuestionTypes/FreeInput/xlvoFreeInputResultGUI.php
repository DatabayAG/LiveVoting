<?php

declare(strict_types=1);

namespace LiveVoting\QuestionTypes\FreeInput;

use LiveVoting\QuestionTypes\xlvoResultGUI;
use LiveVoting\Vote\xlvoVote;

class xlvoFreeInputResultGUI extends xlvoResultGUI
{
    /**
     * @param xlvoVote[] $votes
     */
    public function getAPIRepresentation(array $votes): string
    {
        return $this->getTextRepresentation($votes);
    }

    /**
     * @param xlvoVote[] $votes
     */
    public function getTextRepresentation(array $votes): string
    {
        $strings = [];
        foreach ($votes as $vote) {
            $strings[] = str_replace(["\r\n", "\r", "\n"], " ", $vote->getFreeInput());
        }

        return implode(', ', $strings);
    }
}
