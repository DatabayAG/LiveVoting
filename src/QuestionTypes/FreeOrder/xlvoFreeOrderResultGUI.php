<?php

declare(strict_types=1);

namespace LiveVoting\QuestionTypes\FreeOrder;

use LiveVoting\Option\xlvoOption;
use LiveVoting\QuestionTypes\xlvoResultGUI;
use LiveVoting\Vote\xlvoVote;

class xlvoFreeOrderResultGUI extends xlvoResultGUI
{
    /**
     * @param xlvoVote[] $votes
     */
    public function getTextRepresentation(array $votes): string
    {
        $strings = array();
        if (!count($votes)) {
            return "";
        }

        $vote = array_shift($votes);
        $json_decode = json_decode($vote->getFreeInput(), false, 512, JSON_THROW_ON_ERROR);
        if (!is_array($json_decode)) {
            return "";
        }
        foreach ($json_decode as $option_id) {
            $xlvoOption = $this->options[$option_id];
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
        $strings = array();
        if (!count($votes)) {
            return "";
        }

        $vote = array_shift($votes);
        $json_decode = json_decode($vote->getFreeInput(), false, 512, JSON_THROW_ON_ERROR);
        if (!is_array($json_decode)) {
            return "";
        }
        foreach ($json_decode as $option_id) {
            $strings[] = $this->options[$option_id]->getText();
        }

        return implode(", ", $strings);
    }
}
