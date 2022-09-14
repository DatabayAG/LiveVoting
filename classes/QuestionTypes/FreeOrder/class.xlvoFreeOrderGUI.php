<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use LiveVoting\Js\xlvoJs;
use LiveVoting\QuestionTypes\xlvoQuestionTypes;
use LiveVoting\UIComponent\GlyphGUI;

/**
 * Class xlvoFreeOrderGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy xlvoFreeOrderGUI: xlvoVoter2GUI
 */
class xlvoFreeOrderGUI extends xlvoCorrectOrderGUI
{
    protected function isRandomizeOptions(): bool
    {
        return false;
    }

    protected function isShowCorrectOrder(): bool
    {
        return false;
    }

    public function getMobileHTML(): string
    {
        return $this->getFormContent() . xlvoJs::getInstance()->name(xlvoQuestionTypes::FREE_ORDER)->category(
            'QuestionTypes'
        )->getRunCode();
    }

    public function initJS(bool $current = false): void
    {
        xlvoJs::getInstance()->api($this)->name(xlvoQuestionTypes::FREE_ORDER)->category('QuestionTypes')
              ->addLibToHeader('jquery.ui.touch-punch.min.js')->init();
    }

    public function getButtonInstances(): array
    {
        if (!$this->manager->getPlayer()->isShowResults()) {
            return [];
        }
        $states = $this->getButtonsStates();
        $b = ilLinkButton::getInstance();
        $b->setId(self::BUTTON_TOTTLE_DISPLAY_CORRECT_ORDER);
        if ($states[self::BUTTON_TOTTLE_DISPLAY_CORRECT_ORDER]) {
            $b->setCaption(GlyphGUI::get('align-left'), false);
        } else {
            $b->setCaption(GlyphGUI::get('sort-by-attributes-alt'), false);
        }

        //		$t = ilLinkButton::getInstance();
        //		$t->setId(self::BUTTON_TOGGLE_PERCENTAGE);
        //		if ($states[self::BUTTON_TOGGLE_PERCENTAGE]) {
        //			$t->setCaption(' %', false);
        //		} else {
        //			$t->setCaption(GlyphGUI::get('user'), false);
        //		}

        return [$b];
    }
}
