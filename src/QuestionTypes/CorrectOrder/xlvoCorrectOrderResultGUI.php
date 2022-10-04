<?php

declare(strict_types=1);

namespace LiveVoting\QuestionTypes\CorrectOrder;

use LiveVoting\Option\xlvoOption;
use LiveVoting\QuestionTypes\xlvoResultGUI;
use LiveVoting\Vote\xlvoVote;

class xlvoCorrectOrderResultGUI extends xlvoResultGUI
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

        $correct_order_json = $this->getCorrectOrderJSON();
        $return = ($correct_order_json === $vote->getFreeInput())
            ? self::plugin()->translate("common_correct_order")
            : self::plugin()
                  ->translate("common_incorrect_order");
        $return .= ": ";
        foreach (json_decode($vote->getFreeInput(), false, 512, JSON_THROW_ON_ERROR) as $option_id) {
            $xlvoOption = $this->options[$option_id];
            if ($xlvoOption instanceof xlvoOption) {
                $strings[] = $xlvoOption->getTextForPresentation();
            } else {
                $strings[] = self::plugin()->translate("common_option_no_longer_available");
            }
        }

        return $return . implode(", ", $strings);
    }

    protected function getCorrectOrderJSON(): string
    {
        $correct_order_ids = array();
        foreach ($this->options as $option) {
            $correct_order_ids[(int) $option->getCorrectPosition()] = $option->getId();
        }
        ksort($correct_order_ids);
        return json_encode(array_values($correct_order_ids), JSON_THROW_ON_ERROR);
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
        $correct_order_json = $this->getCorrectOrderJSON();
        $return = ($correct_order_json === $vote->getFreeInput())
            ? self::plugin()->translate("common_correct_order")
            : self::plugin()
                  ->translate("common_incorrect_order");
        $return .= ": ";
        foreach (json_decode($vote->getFreeInput(), false, 512, JSON_THROW_ON_ERROR) as $option_id) {
            $strings[] = $this->options[$option_id]->getText();
        }

        return $return . implode(", ", $strings);
    }
}
