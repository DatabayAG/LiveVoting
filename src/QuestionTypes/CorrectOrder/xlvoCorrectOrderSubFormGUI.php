<?php

declare(strict_types=1);

namespace LiveVoting\QuestionTypes\CorrectOrder;

use ilCheckboxInputGUI;
use ilException;
use ilFormPropertyGUI;
use ilNumberInputGUI;
use InvalidArgumentException;
use LiveVoting\Exceptions\xlvoSubFormGUIHandleFieldException;
use LiveVoting\Option\xlvoOption;
use LiveVoting\QuestionTypes\xlvoSubFormGUI;
use srag\CustomInputGUIs\LiveVoting\MultiLineNewInputGUI\MultiLineNewInputGUI;
use srag\CustomInputGUIs\LiveVoting\TextInputGUI\TextInputGUI;
use srag\CustomInputGUIs\LiveVoting\HiddenInputGUI\HiddenInputGUI;
use ilGlobalPageTemplate;

class xlvoCorrectOrderSubFormGUI extends xlvoSubFormGUI
{
    public const F_OPTIONS = 'options';
    public const F_TEXT = 'text';
    public const F_ID = 'id';
    public const F_POSITION = 'position';
    public const F_CORRECT_POSITION = 'correct_position';
    public const OPTION_RANDOMIZE_OPTIONS_AFTER_SAVE = 'option_randomise_option_after_save';
    public const OPTION_RANDOMIZE_OPTIONS_AFTER_SAVE_INFO = 'option_randomise_option_after_save_info';
    public const CSS_FILE_PATH = './Customizing/global/plugins/Services/Repository/RepositoryObject/LiveVoting/templates/default/QuestionTypes/CorrectOrder/correct_order_form.css';

    /** @var xlvoOption[] */
    protected array $options = array();

    protected function initFormElements(): void
    {
        $xlvoMultiLineInputGUI = new MultiLineNewInputGUI($this->txt(self::F_OPTIONS), self::F_OPTIONS);
        $xlvoMultiLineInputGUI->setShowInputLabel(0);
        $xlvoMultiLineInputGUI->setShowSort(false);

        $randomiseOptionSequenceAfterSave = new ilCheckboxInputGUI(
            $this->txt(self::OPTION_RANDOMIZE_OPTIONS_AFTER_SAVE),
            self::OPTION_RANDOMIZE_OPTIONS_AFTER_SAVE
        );
        $randomiseOptionSequenceAfterSave->setOptionTitle($this->txt(self::OPTION_RANDOMIZE_OPTIONS_AFTER_SAVE_INFO));
        //$xlvoMultiLineInputGUI->setPositionMovable(true); // Allow move position
        $randomiseOptionSequenceAfterSave->setChecked(
            $this->getXlvoVoting()->getRandomiseOptionSequence()
        ); // Should shuffled?

        $h = new HiddenInputGUI(self::F_ID);
        $xlvoMultiLineInputGUI->addInput($h);

        /*if (!$this->getXlvoVoting()->getRandomiseOptionSequence()) {
            // Allow input correct position if not shuffled*/
        $position = new ilNumberInputGUI($this->txt('option_correct_position'), self::F_CORRECT_POSITION);
        $position->setRequired(true);
        $position->setMinValue(1);
        $position->setSize(2);
        $position->setMaxLength(2);
        /*} else {
            // Only display correct order as text if shuffled
            $position = new ilNonEditableValueGUI("", self::F_CORRECT_POSITION, true);
        }*/
        $xlvoMultiLineInputGUI->addInput($position);

        $te = new TextInputGUI($this->txt('option_text'), self::F_TEXT);
        $xlvoMultiLineInputGUI->addInput($te);

        $this->addFormElement($randomiseOptionSequenceAfterSave);
        $this->addFormElement($xlvoMultiLineInputGUI);
    }

    /**
     * @param string|array      $value
     * @throws xlvoSubFormGUIHandleFieldException|ilException
     */
    protected function handleField(ilFormPropertyGUI $element, $value): void
    {
        switch ($element->getPostVar()) {
            case self::F_OPTIONS:
                $pos = 1;
                foreach ($value as $item) {
                    /**
                     * @var xlvoOption $xlvoOption
                     */
                    $xlvoOption = xlvoOption::findOrGetInstance($item[self::F_ID]);
                    $xlvoOption->setText($item[self::F_TEXT]);
                    $xlvoOption->setStatus(xlvoOption::STAT_ACTIVE);
                    $xlvoOption->setVotingId($this->getXlvoVoting()->getId());
                    $xlvoOption->setPosition($pos);
                    /*if (!$this->getXlvoVoting()->getRandomiseOptionSequence()) {
                        // Correct position can only be inputed if not shuffle*/
                    $xlvoOption->setCorrectPosition($item[self::F_CORRECT_POSITION]);
                    /*}*/
                    $xlvoOption->setType((int) $this->getXlvoVoting()->getVotingType());
                    $this->options[] = $xlvoOption;
                    $pos++;
                }
                break;
            case self::OPTION_RANDOMIZE_OPTIONS_AFTER_SAVE:
                $value = (bool) $value;
                $this->getXlvoVoting()->setRandomiseOptionSequence($value);
                break;
            default:
                throw new ilException('Unknown element can not set the value.');
        }
    }

    /**
     * @return array|bool
     * @throws ilException
     */
    protected function getFieldValue(ilFormPropertyGUI $element)
    {
        if ($this->getXlvoVoting()->getRandomiseOptionSequence()) {
            // Sort options by correct position if shuffled
            $this->options = xlvoOption::where(["voting_id" => $this->getXlvoVoting()->getId()])->orderBy(
                "correct_position"
            )->get();
        } else {
            // Sort options by position if not shuffled
            $this->options = $this->getXlvoVoting()->getVotingOptions();
        }
        switch ($element->getPostVar()) {
            case self::F_OPTIONS:
                $array = [];
                foreach ($this->options as $option) {
                    $array[] = [
                        self::F_ID => $option->getId(),
                        self::F_TEXT => $option->getTextForEditor(),
                        self::F_POSITION => $option->getPosition(),
                        self::F_CORRECT_POSITION => /*($this->getXlvoVoting()->getRandomiseOptionSequence() ? "<br>" : "")
                            . */
                            $option->getCorrectPosition()/* . ($this->getXlvoVoting()->getRandomiseOptionSequence() ? "." : "")
                        // Display as text whit dot and break if shuffled otherwise only position for input*/
                    ];
                }

                return $array;
            case self::OPTION_RANDOMIZE_OPTIONS_AFTER_SAVE:
                return $this->getXlvoVoting()->getRandomiseOptionSequence();
            default:
                throw new ilException('Unknown element can not get the value.');
                break;
        }
    }

    protected function handleOptions(): void
    {
        $ids = array();
        foreach ($this->options as $xlvoOption) {
            $xlvoOption->setVotingId($this->getXlvoVoting()->getId());
            $xlvoOption->store();
            $ids[] = $xlvoOption->getId();
        }
        $options = $this->getXlvoVoting()->getVotingOptions();

        foreach ($options as $xlvoOption) {
            if (!in_array($xlvoOption->getId(), $ids, true)) {
                $xlvoOption->delete();
            }
        }

        //randomize the order on save
        if ($this->getXlvoVoting()->getRandomiseOptionSequence()) {
            /*// First set correct position in the sequence of user has ordered
            foreach ($this->options as $i => $option) {
                $option->setCorrectPosition($option->getPosition());
            }*/
            // Then shuffle positions
            $this->randomiseOptionPosition($this->options);
        }

        foreach ($this->options as $option) {
            $option->store();
        }

        $this->getXlvoVoting()->setMultiFreeInput(true);
        $this->getXlvoVoting()->store();
    }

    /**
     * @param xlvoOption[] $options The options which should be randomised.
     */
    private function randomiseOptionPosition(array &$options): void
    {
        //reorder only if there is something to reorder
        if (count($options) < 2) {
            return;
        }

        $optionsLength = count($options);
        foreach ($options as $option) {
            $newPosition = random_int(1, $optionsLength);
            $previousOption = $this->findOptionByPosition($options, $newPosition);
            $previousOption->setPosition($option->getPosition());
            $option->setPosition($newPosition);
        }

        //check if we got the correct result
        if ($this->isNotCorrectlyOrdered($options)) {
            return;
        }

        //we got the right result reshuffle
        $this->randomiseOptionPosition($options);
    }

    /**
     * @param xlvoOption[] $options The options which should be used to search the position.
     * @throws InvalidArgumentException Thrown if the position is not found within the given options.
     */
    private function findOptionByPosition(array $options, int $position): xlvoOption
    {
        foreach ($options as $option) {
            if ($option->getPosition() === $position) {
                return $option;
            }
        }

        throw new InvalidArgumentException("Supplied position \"$position\" can't be found within the given options.");
    }

    /**
     * @param xlvoOption[] $options The options which should be checked.
     */
    private function isNotCorrectlyOrdered(array $options): bool
    {
        $incorrectOrder = 0;
        foreach ($options as $option) {
            if (strcmp($option->getCorrectPosition(), (string) $option->getPosition()) !== 0) {
                $incorrectOrder++;
            }
        }

        return $incorrectOrder > 0;
    }

    public function addJsAndCss(ilGlobalPageTemplate $ilTemplate): void
    {
        $ilTemplate->addCSS(self::CSS_FILE_PATH);
    }
}
