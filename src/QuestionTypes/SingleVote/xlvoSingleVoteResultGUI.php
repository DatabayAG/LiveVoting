<?php

declare(strict_types=1);

namespace LiveVoting\QuestionTypes\SingleVote;

use LiveVoting\Option\xlvoOption;
use LiveVoting\QuestionTypes\xlvoResultGUI;
use LiveVoting\Vote\xlvoVote;

class xlvoSingleVoteResultGUI extends xlvoResultGUI
{
    /**
     * @param xlvoVote[] $votes
     */
    public function getTextRepresentation(array $votes): string
    {
        if (!count($votes)) {
            return "";
        }
        $strings = array();
        foreach ($votes as $vote) {
            $xlvoOption = $this->options[$vote->getOptionId()];
            if ($xlvoOption instanceof xlvoOption) {
                $strings[] = $xlvoOption->getTextForPresentation();
            } else {
                $strings[] = self::plugin()->translate("common_option_no_longer_available");
            }
        }

        return implode(", ", $strings);
    }

    /**
     * @param xlvoVote[] $votes
     */
    public function getAPIRepresentation(array $votes): string
    {
        if (!count($votes)) {
            return "";
        }
        $strings = array();
        foreach ($votes as $vote) {
            $strings[] = $this->options[$vote->getOptionId()]->getText();
        }

        return implode(", ", $strings);
    }
}
