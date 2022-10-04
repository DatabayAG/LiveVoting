<?php

declare(strict_types=1);

namespace LiveVoting\QuestionTypes\NumberRange;

use ilCheckboxInputGUI;
use ilException;
use ilFormPropertyGUI;
use ilNumberInputGUI;
use ilRadioGroupInputGUI;
use ilRadioOption;
use LiveVoting\Exceptions\xlvoSubFormGUIHandleFieldException;
use LiveVoting\QuestionTypes\xlvoSubFormGUI;

/**
 * This class supplies the gui for the manage view, which is used
 * to update und create new question types.
 */
class xlvoNumberRangeSubFormGUI extends xlvoSubFormGUI
{
    public const OPTION_PERCENTAGE = 'option_percentage';
    public const OPTION_PERCENTAGE_INFO = 'option_percentage_info';
    public const OPTION_ALTERNATIVE_RESULT_DISPLAY_MODE = 'option_alternative_result_display_mode';
    public const OPTION_ALTERNATIVE_RESULT_DISPLAY_MODE_INFO = 'option_alternative_result_display_mode_info';
    public const OPTION_RANGE_START = 'option_range_start';
    public const OPTION_RANGE_START_INFO = 'option_range_start_info';
    public const OPTION_RANGE_END = 'option_range_end';
    public const OPTION_RANGE_END_INFO = 'option_range_end_info';
    public const OPTION_RANGE_STEP = 'option_range_step';
    public const OPTION_RANGE_STEP_INFO = 'option_range_step_info';
    public const START_RANGE_INVALID_INFO = 'qtype_6_invalid_start_range';
    public const END_RANGE_INVALID_INFO = 'qtype_6_invalid_end_range';
    public const START_RANGE_MIN = -1000000;
    public const START_RANGE_MAX = 1000000;
    public const END_RANGE_MIN = -1000000;
    public const END_RANGE_MAX = 1000000;
    public const STEP_RANGE_MIN = 1;
    //const STEP_RANGE_MAX = 1000000;
    public const STEP_RANGE_DEFAULT_VALUE = 1;
    public const STEP_RANGE_INVALID_INFO = 'qtype_6_invalid_step_range';

    protected function initFormElements(): void
    {
        //create percentage check box
        $percentageCheckBox = new ilCheckboxInputGUI($this->txt(self::OPTION_PERCENTAGE), self::OPTION_PERCENTAGE);
        $percentageCheckBox->setInfo($this->txt(self::OPTION_PERCENTAGE_INFO));
        $percentageCheckBox->setChecked(((int) $this->getXlvoVoting()->getStartRange()) === 1);

        //create badge box option for the result display
        $alternativeResultDisplayModeCheckBox = new ilCheckboxInputGUI(
            $this->txt(self::OPTION_ALTERNATIVE_RESULT_DISPLAY_MODE),
            self::OPTION_ALTERNATIVE_RESULT_DISPLAY_MODE
        );
        $alternativeResultDisplayModeCheckBox->setInfo($this->txt(self::OPTION_ALTERNATIVE_RESULT_DISPLAY_MODE_INFO));
        $alternativeResultDisplayModeCheckBox->setChecked(
            ((int) $this->getXlvoVoting()->getAltResultDisplayMode()) === 1
        );

        $displayMode = new ilRadioGroupInputGUI(
            $this->txt(self::OPTION_ALTERNATIVE_RESULT_DISPLAY_MODE),
            self::OPTION_ALTERNATIVE_RESULT_DISPLAY_MODE
        );
        $displayMode->addOption(
            new ilRadioOption(
                $this->txt(
                    'display_mode_nr_'
                    . xlvoNumberRangeResultsGUI::DISPLAY_MODE_GROUPED_TEXT
                ),
                (string) xlvoNumberRangeResultsGUI::DISPLAY_MODE_GROUPED_TEXT
            )
        );
        $displayMode->addOption(
            new ilRadioOption(
                $this->txt(
                    'display_mode_nr_'
                    . xlvoNumberRangeResultsGUI::DISPLAY_MODE_GROUPED_TEXT_EXTENDED
                ),
                (string) xlvoNumberRangeResultsGUI::DISPLAY_MODE_GROUPED_TEXT_EXTENDED
            )
        );
        $displayMode->addOption(
            new ilRadioOption(
                $this->txt(
                    'display_mode_nr_'
                    . xlvoNumberRangeResultsGUI::DISPLAY_MODE_BARS
                ),
                (string) xlvoNumberRangeResultsGUI::DISPLAY_MODE_BARS
            )
        );

        $displayMode->setValue((string) $this->getXlvoVoting()->getAltResultDisplayMode());

        //create start range number input
        $startRange = new ilNumberInputGUI($this->txt(self::OPTION_RANGE_START), self::OPTION_RANGE_START);
        $startRange->setInfo($this->txt(self::OPTION_RANGE_START_INFO));
        $startRange->setMinValue(self::START_RANGE_MIN);
        $startRange->setMaxValue(self::START_RANGE_MAX);
        $startRange->setValue((string) $this->getXlvoVoting()->getStartRange());
        $startRange->allowDecimals(false);

        //create end range number input
        $endRange = new ilNumberInputGUI($this->txt(self::OPTION_RANGE_END), self::OPTION_RANGE_END);
        $endRange->setInfo($this->txt(self::OPTION_RANGE_END_INFO));
        $endRange->setMinValue(self::END_RANGE_MIN);
        $endRange->setMaxValue(self::END_RANGE_MAX);
        $endRange->setValue((string) $this->getXlvoVoting()->getEndRange());

        //create end range number input
        $stepRange = new ilNumberInputGUI($this->txt(self::OPTION_RANGE_STEP), self::OPTION_RANGE_STEP);
        $stepRange->setInfo($this->txt(self::OPTION_RANGE_STEP_INFO));
        $stepRange->setMinValue(self::STEP_RANGE_MIN);
        //$stepRange->setMaxValue(self::STEP_RANGE_MAX);
        $stepRange->setValue((string) $this->getXlvoVoting()->getStepRange());

        //add elements to gui
        $this->addFormElement($percentageCheckBox);
        $this->addFormElement($displayMode);
        //$this->addFormElement($alternativeResultDisplayModeCheckBox);
        $this->addFormElement($startRange);
        $this->addFormElement($endRange);
        $this->addFormElement($stepRange);
    }

    /**
     * @param ilFormPropertyGUI $element
     * @param string|array      $value
     *
     * @throws xlvoSubFormGUIHandleFieldException|ilException
     */
    protected function handleField(ilFormPropertyGUI $element, $value): void
    {
        $postKey = $element->getPostVar();
        $value = (int) $value;

        switch ($postKey) {
            case self::OPTION_PERCENTAGE:
                $this->getXlvoVoting()->setPercentage($value === 1 ? 1 : 0); //if the value is 1 set 1 or else 0.
                break;
            case self::OPTION_RANGE_START:
                $this->getXlvoVoting()->setStartRange($value);
                break;
            case self::OPTION_RANGE_END:
                $this->getXlvoVoting()->setEndRange($value);
                break;
            case self::OPTION_RANGE_STEP:
                $this->getXlvoVoting()->setStepRange($value);
                break;
            case self::OPTION_ALTERNATIVE_RESULT_DISPLAY_MODE:
                $this->getXlvoVoting()->setAltResultDisplayMode((int) $value); //if the value is 1 set 1 or else 0.
                break;
            default:
                throw new ilException('Unknown element can not set the value.');
        }
    }

    /**
     * @throws ilException
     */
    protected function getFieldValue(ilFormPropertyGUI $element): int
    {
        $postKey = $element->getPostVar();

        switch ($postKey) {
            case self::OPTION_PERCENTAGE:
                return $this->getXlvoVoting()->getPercentage();
            case self::OPTION_RANGE_START:
                return $this->getXlvoVoting()->getStartRange();
            case self::OPTION_RANGE_END:
                return $this->getXlvoVoting()->getEndRange();
            case self::OPTION_RANGE_STEP:
                return $this->getXlvoVoting()->getStepRange();
            case self::OPTION_ALTERNATIVE_RESULT_DISPLAY_MODE:
                return $this->getXlvoVoting()->getAltResultDisplayMode();
            default:
                throw new ilException('Unknown element can not get the value.');
                break;
        }
    }

    /**
     * @throws xlvoSubFormGUIHandleFieldException
     */
    protected function validateForm(): void
    {
        $end = $this->getXlvoVoting()->getEndRange();
        $start = $this->getXlvoVoting()->getStartRange();
        $step = $this->getXlvoVoting()->getStepRange();
        $range = ($end - $start);

        if (!($start < $end && $start <= self::START_RANGE_MAX && $start >= self::START_RANGE_MIN)) {
            throw new xlvoSubFormGUIHandleFieldException(
                self::plugin()->translate(self::START_RANGE_INVALID_INFO, "", [
                    self::START_RANGE_MIN,
                    self::START_RANGE_MAX
                ])
            );
        }

        if (!($end > $start && $end <= self::END_RANGE_MAX && $end >= self::END_RANGE_MIN)) {
            throw new xlvoSubFormGUIHandleFieldException(
                self::plugin()->translate(self::END_RANGE_INVALID_INFO, "", [
                    self::END_RANGE_MIN,
                    self::END_RANGE_MAX
                ])
            );
        }

        if (!($step < $range && $range % $step === 0)) {
            throw new xlvoSubFormGUIHandleFieldException(self::plugin()->translate(self::STEP_RANGE_INVALID_INFO));
        }
    }
}
