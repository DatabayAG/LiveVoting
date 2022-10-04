<?php

declare(strict_types=1);

namespace LiveVoting\QuestionTypes\NumberRange;

use LiveVoting\Display\Bar\xlvoBarCollectionGUI;
use LiveVoting\Display\Bar\xlvoBarFreeInputsGUI;
use LiveVoting\Display\Bar\xlvoBarGroupingCollectionGUI;
use LiveVoting\Display\Bar\xlvoBarInfoGUI;
use LiveVoting\Display\Bar\xlvoBarPercentageGUI;
use LiveVoting\QuestionTypes\xlvoInputResultsGUI;
use LiveVoting\Vote\xlvoVote;

class xlvoNumberRangeResultsGUI extends xlvoInputResultsGUI
{
    public const BAR_COUNT = 5;
    public const DISPLAY_MODE_GROUPED_TEXT = 0;
    public const DISPLAY_MODE_BARS = 1;
    public const DISPLAY_MODE_GROUPED_TEXT_EXTENDED = 2;

    public function getHTML(): string
    {
        switch ($this->voting->getAltResultDisplayMode()) {
            case self::DISPLAY_MODE_BARS:
                return $this->renderBarResult();
            case self::DISPLAY_MODE_GROUPED_TEXT_EXTENDED:
                return $this->renderGroupedTextResultWithInfo();
            case self::DISPLAY_MODE_GROUPED_TEXT:
            default:
                return $this->renderGroupedTextResult();
        }
    }

    private function renderBarResult(): string
    {
        $values = $this->getAllVoteValues();

        $bars = new xlvoBarCollectionGUI();
        $voteSum = array_sum($values);

        foreach ($values as $key => $value) {
            $bar = new xlvoBarPercentageGUI();
            $bar->setMaxVotes($voteSum);
            $bar->setVotes((int) $value);
            $bar->setTitle($key);
            $bars->addBar($bar);
        }

        return $bars->getHTML();
    }

    /**
     * @return string[]
     */
    private function getAllVoteValues(): array
    {
        $percentage = ($this->manager->getVoting()->getPercentage() === 1) ? ' %' : '';

        //generate array which is equal in its length to the range from start to end
        $start = $this->manager->getVoting()->getStartRange();
        $end = $this->manager->getVoting()->getEndRange();
        $count = ($end - $start);
        $values = array_fill($start, ($count + 1), 0);

        $votes = $this->manager->getVotesOfVoting();

        //count all votes per option
        /**
         * @var xlvoVote $vote
         */
        foreach ($votes as $vote) {
            $value = (int) $vote->getFreeInput();
            $values[$value]++;
        }

        //Create 10 slices and sum each slice
        $slices = [];
        $sliceWidth = ceil($count / self::BAR_COUNT);

        for ($i = 0; $i < $count; $i += $sliceWidth) {
            //create a slice
            $slice = array_slice($values, $i, $sliceWidth + (($i + $sliceWidth >= $count) ? 1 : 0), true);

            //sum slice values
            $sum = array_sum($slice);

            //fetch keys to generate new key for slices
            $keys = array_keys($slice);
            $keyCount = count($keys);

            //only display a range if we got more than one element
            if ($keyCount > 1) {
                $key = "$keys[0]$percentage - {$keys[$keyCount - 1]}$percentage";
            } else {
                $key = "$keys[0]$percentage";
            }

            //create now slice entry
            $slices[$key] = $sum;
        }

        return $slices;
    }

    private function renderGroupedTextResultWithInfo(): string
    {
        $votes = $this->manager->getVotesOfVoting();
        $vote_count = $this->manager->countVotes();

        $vote_sum = 0;
        $values = [];
        $modes = [];

        array_walk($votes, static function (xlvoVote $vote) use (&$vote_sum, &$values, &$modes) {
            $value = (int) $vote->getFreeInput();
            $values[] = $value;
            $modes[$value]++;
            $vote_sum += $value;
        });
        $relevant_modes = [];
        foreach ($modes as $given_value => $counter) {
            if ($counter === max($modes)) {
                $relevant_modes[] = $given_value;
            }
        }

        $calculateMedian = static function ($aValues) {
            $aToCareAbout = array();
            foreach ($aValues as $mValue) {
                if ($mValue >= 0) {
                    $aToCareAbout[] = $mValue;
                }
            }
            $iCount = count($aToCareAbout);
            sort($aToCareAbout, SORT_NUMERIC);
            if ($iCount > 2) {
                if ($iCount % 2 === 0) {
                    return ($aToCareAbout[floor($iCount / 2) - 1] + $aToCareAbout[floor($iCount / 2)]) / 2;
                }

                return $aToCareAbout[$iCount / 2];
            }

            return $aToCareAbout[0] ?? 0;
        };

        $info = new xlvoBarCollectionGUI();
        $value = $vote_count > 0 ? round($vote_sum / $vote_count, 2) : 0;
        $mean = new xlvoBarInfoGUI($this->txt("mean"), (string) $value);
        $mean->setBig(true);
        $mean->setDark(true);
        $mean->setCenter(true);
        $info->addBar($mean);

        $median = new xlvoBarInfoGUI($this->txt("median"), $calculateMedian($values));
        $median->setBig(true);
        $median->setCenter(true);
        $median->setDark(true);
        $info->addBar($median);

        $mode = new xlvoBarInfoGUI(
            $this->txt("mode"),
            count($relevant_modes) === 1 ? $relevant_modes[0] : $this->txt("mode_not_applicable")
        );
        $mode->setBig(true);
        $mode->setDark(true);
        $mode->setCenter(true);
        $info->addBar($mode);

        return $info->getHTML() . "<div class='row'><br></div>" . $this->renderGroupedTextResult();
    }

    private function renderGroupedTextResult(): string
    {
        $bars = new xlvoBarGroupingCollectionGUI();
        //$bars->sorted(true);
        $votes = $this->manager->getVotesOfVoting();
        usort($votes, static function (xlvoVote $v1, xlvoVote $v2) {
            return ((int) $v1->getFreeInput() - (int) $v2->getFreeInput());
        });
        foreach ($votes as $value) {
            $bar = new xlvoBarFreeInputsGUI($this->voting, $value);
            $bar->setBig(true);
            $bar->setCenter(true);
            $bars->addBar($bar);
        }

        return $bars->getHTML();
    }

    /**
     * @param xlvoVote[] $votes
     */
    public function getTextRepresentationForVotes(array $votes): string
    {
        return xlvoNumberRangeResultGUI::getInstance($this->voting)->getTextRepresentation($votes);
    }
}
