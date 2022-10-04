<?php

declare(strict_types=1);

namespace LiveVoting\QuestionTypes;

use ilButtonBase;
use ilException;
use LiveVoting\GUI\xlvoGUI;
use LiveVoting\Voting\xlvoVotingManager2;
use xlvoCorrectOrderGUI;
use xlvoFreeInputGUI;
use xlvoFreeOrderGUI;
use xlvoNumberRangeGUI;
use xlvoSingleVoteGUI;
use xlvoVoter2GUI;

abstract class xlvoQuestionTypesGUI extends xlvoGUI
{
    public const CMD_SUBMIT = 'submit';
    protected xlvoVotingManager2 $manager;
    protected bool $show_question = true;
    protected bool $has_solution = false;

    /**
     *
     * @return xlvoQuestionTypesGUI
     * @throws ilException                 Throws an ilException if no gui class was found.
     */
    public static function getInstance(xlvoVotingManager2 $manager, int $override_type = null): self
    {
        $class = xlvoQuestionTypes::getClassName(
            (int) ($override_type ?: $manager->getVoting()->getVotingType())
        );

        $gui = null;
        switch ($class) {
            case xlvoQuestionTypes::CORRECT_ORDER:
                $gui = new xlvoCorrectOrderGUI();
                break;
            case xlvoQuestionTypes::FREE_INPUT:
                $gui = new xlvoFreeInputGUI();
                break;
            case xlvoQuestionTypes::FREE_ORDER:
                $gui = new xlvoFreeOrderGUI();
                break;
            case xlvoQuestionTypes::SINGLE_VOTE:
                $gui = new xlvoSingleVoteGUI();
                break;
            case xlvoQuestionTypes::NUMBER_RANGE:
                $gui = new xlvoNumberRangeGUI();
                break;
            default:
                throw new ilException("Could not find the gui for the current voting.");
        }

        $gui->setManager($manager);

        return $gui;
    }

    protected function txt(string $key): string
    {
        return self::plugin()->translate($this->manager->getVoting()->getVotingType() . '_' . $key, 'qtype');
    }

    abstract protected function submit();

    protected function getButtonsStates(): array
    {
        return $this->getManager()->getPlayer()->getButtonStates();
    }

    public function executeCommand(): void
    {
        $nextClass = self::dic()->ctrl()->getNextClass();

        switch ($nextClass) {
            default:
                $cmd = self::dic()->ctrl()->getCmd(self::CMD_STANDARD);

                $this->{$cmd}();
                if ($cmd === self::CMD_SUBMIT) {
                    $this->afterSubmit();
                }
                break;
        }
        if ($this->is_api_call) {
            self::output()->output("", true);
        }
    }

    protected function afterSubmit(): void
    {
        self::dic()->ctrl()->redirect(new xlvoVoter2GUI(), xlvoVoter2GUI::CMD_START_VOTER_PLAYER);
    }

    public function isShowQuestion(): bool
    {
        return $this->show_question;
    }

    public function setShowQuestion(bool $show_question): void
    {
        $this->show_question = $show_question;
    }

    abstract public function initJS(bool $current = false): void;

    abstract public function getMobileHTML(): string;

    public function handleButtonCall(int $button_id, $data): void
    {
        $this->saveButtonState($button_id, $data);
    }


    //
    // Custom Buttons
    //

    protected function saveButtonState(int $button_id, $state): void
    {
        $xlvoPlayer = $this->getManager()->getPlayer();
        $states = $xlvoPlayer->getButtonStates();
        $states[$button_id] = $state;
        $xlvoPlayer->setButtonStates($states);
        $xlvoPlayer->store();
    }

    public function getManager(): xlvoVotingManager2
    {
        return $this->manager;
    }

    public function setManager(xlvoVotingManager2 $manager): void
    {
        $this->manager = $manager;
    }

    public function hasButtons(): bool
    {
        return (count($this->getButtonInstances()) > 0);
    }

    /**
     * @return ilButtonBase[]
     */
    public function getButtonInstances(): array
    {
        return [];
    }
}
