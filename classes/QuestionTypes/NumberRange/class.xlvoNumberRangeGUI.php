<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use LiveVoting\Js\xlvoJs;
use LiveVoting\QuestionTypes\xlvoQuestionTypes;
use LiveVoting\QuestionTypes\xlvoQuestionTypesGUI;
use LiveVoting\Vote\xlvoVote;
use LiveVoting\Voting\xlvoVotingManager2;

/**
 * @author            Nicolas Schäfli <ns@studer-raimann.ch>
 * @ilCtrl_IsCalledBy xlvoNumberRangeGUI: xlvoVoter2GUI
 */
class xlvoNumberRangeGUI extends xlvoQuestionTypesGUI
{
    public const USER_SELECTED_NUMBER = 'user_selected_number';
    public const SAVE_BUTTON_VOTE = 'voter_start_button_vote';
    public const CLEAR_BUTTON = 'voter_clear';
    public const SAVE_BUTTON_UNVOTE = 'voter_start_button_unvote';

    protected function clear(): void
    {
        $this->manager->unvoteAll();
        $this->afterSubmit();
    }

    protected function submit(): void
    {
        if ($this->manager === null) {
            throw new ilException('The NumberRange question got no voting manager! Please set one via setManager.');
        }

        //get all votes of the currents user
        // $votes = $this->manager->getVotesOfUser(false); TODO: ???

        //check if we voted or unvoted

        //we voted

        //filter the input and convert to int
        $filteredInput = filter_input(INPUT_POST, self::USER_SELECTED_NUMBER, FILTER_VALIDATE_INT);

        //check if the filter failed
        if ($filteredInput !== false && $filteredInput !== null) {
            //filter succeeded set value and store vote

            //validate user input
            if ($this->isVoteValid($this->getStart(), $this->getEnd(), $filteredInput)) {
                //vote
                $this->manager->inputOne([
                    'input' => $filteredInput,
                    'vote_id' => '-1',
                ]);
            }
        }
    }

    private function isVoteValid(int $start, int $end, float $value): bool
    {
        return ($value >= $start && $value <= $end && (int) $value === $this->snapToStep($value));
    }

    private function snapToStep(float $value): int
    {
        return (int) (ceil(($value - $this->getStart()) / $this->getStep()) * $this->getStep()) + $this->getStart();
    }

    private function getStart(): int
    {
        return $this->manager->getVoting()->getStartRange();
    }

    private function getEnd(): int
    {
        return $this->manager->getVoting()->getEndRange();
    }

    /**
     * @throws ilException
     */
    public function setManager(xlvoVotingManager2 $manager): void
    {
        if ($manager === null) {
            throw new ilException('The manager must not be null.');
        }

        parent::setManager($manager);
    }

    public function initJS(bool $current = false): void
    {
        xlvoJs::getInstance()->api($this)->name(xlvoQuestionTypes::NUMBER_RANGE)->category(
            'QuestionTypes'
        )->addLibToHeader('bootstrap-slider.min.js')
              ->addSettings([
                  "step" => $this->getStep()
              ])->init();
    }

    private function getStep(): int
    {
        return $this->manager->getVoting()->getStepRange();
    }

    public function getMobileHTML(): string
    {
        $template = self::plugin()->template('default/QuestionTypes/NumberRange/tpl.number_range.html');
        $template->setVariable('ACTION', self::dic()->ctrl()->getFormAction($this));
        $template->setVariable('SHOW_PERCENTAGE', $this->manager->getVoting()->getPercentage());

        /**
         * @var xlvoVote[] $userVotes
         */
        $userVotes = $this->manager->getVotesOfUser(false);
        $userVotes = array_values($userVotes);

        $template->setVariable('SLIDER_MIN', $this->getStart());
        $template->setVariable('SLIDER_MAX', $this->getEnd());
        $template->setVariable('SLIDER_STEP', $this->getStep());
        if ($userVotes[0] instanceof xlvoVote) {
            $user_has_voted = true;
            $value = (int) $userVotes[0]->getFreeInput();
        } else {
            $user_has_voted = false;
            $value = $this->getDefaultValue();
        }
        $template->setVariable('SLIDER_VALUE', $value);
        $template->setVariable('BTN_SAVE', $this->txt(self::SAVE_BUTTON_VOTE));
        $template->setVariable('BTN_CLEAR', $this->txt(self::CLEAR_BUTTON));

        if (!$user_has_voted) {
            $template->setVariable('BTN_RESET_DISABLED', 'disabled="disabled"');
        }

        return $template->get() . xlvoJs::getInstance()->name(xlvoQuestionTypes::NUMBER_RANGE)->category(
            'QuestionTypes'
        )->getRunCode();
    }

    private function getDefaultValue(): int
    {
        return $this->snapToStep(($this->getStart() + $this->getEnd()) / 2);
    }
}
