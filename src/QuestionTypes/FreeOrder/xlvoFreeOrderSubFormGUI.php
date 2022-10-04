<?php

declare(strict_types=1);

namespace LiveVoting\QuestionTypes\FreeOrder;

use ilException;
use ilFormPropertyGUI;
use LiveVoting\Exceptions\xlvoSubFormGUIHandleFieldException;
use LiveVoting\Option\xlvoOption;
use LiveVoting\QuestionTypes\xlvoSubFormGUI;
use srag\CustomInputGUIs\LiveVoting\MultiLineNewInputGUI\MultiLineNewInputGUI;
use srag\CustomInputGUIs\LiveVoting\TextInputGUI\TextInputGUI;
use srag\CustomInputGUIs\LiveVoting\HiddenInputGUI\HiddenInputGUI;

class xlvoFreeOrderSubFormGUI extends xlvoSubFormGUI
{
    public const F_OPTIONS = 'options';
    public const F_TEXT = 'text';
    public const F_ID = 'id';
    public const F_POSITION = 'position';
    public const F_WEIGHT = 'weight';
    /** @var xlvoOption[] */
    protected array $options = [];

    protected function initFormElements(): void
    {
        $xlvoMultiLineInputGUI = new MultiLineNewInputGUI($this->txt(self::F_OPTIONS), self::F_OPTIONS);
        $xlvoMultiLineInputGUI->setShowInputLabel(0);
        $xlvoMultiLineInputGUI->setShowSort(true);

        $h = new HiddenInputGUI(self::F_ID);
        $xlvoMultiLineInputGUI->addInput($h);

        $te = new TextInputGUI($this->txt('option_text'), self::F_TEXT);

        $xlvoMultiLineInputGUI->addInput($te);

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
                    $xlvoOption->setCorrectPosition($item[self::F_WEIGHT]);
                    $xlvoOption->setType((int) $this->getXlvoVoting()->getVotingType());
                    $this->options[] = $xlvoOption;
                    $pos++;
                }
                break;
            default:
                throw new ilException('Unknown element can not get the value.');
        }
    }

    /**
     * @throws ilException
     */
    protected function getFieldValue(ilFormPropertyGUI $element): array
    {
        switch ($element->getPostVar()) {
            case self::F_OPTIONS:
                $array = [];
                /**
                 * @var xlvoOption $option
                 */
                $options = $this->getXlvoVoting()->getVotingOptions();
                foreach ($options as $option) {
                    $array[] = [
                        self::F_ID => $option->getId(),
                        self::F_TEXT => $option->getTextForEditor(),
                        self::F_POSITION => $option->getPosition(),
                        self::F_WEIGHT => $option->getCorrectPosition(),
                    ];
                }

                return $array;
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
        $this->getXlvoVoting()->setMultiFreeInput(true);
        //$this->getXlvoVoting()->regenerateOptionSorting();
        $this->getXlvoVoting()->store();
    }
}
