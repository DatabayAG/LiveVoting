<?php

declare(strict_types=1);

namespace LiveVoting\Voting;

use ilException;
use ilHiddenInputGUI;
use ilLiveVotingPlugin;
use ilNonEditableValueGUI;
use ilObject;
use ilPropertyFormGUI;
use ilRTE;
use ilSelectInputGUI;
use ilUtil;
use LiveVoting\Exceptions\xlvoSubFormGUIHandleFieldException;
use LiveVoting\QuestionTypes\FreeInput\xlvoFreeInputVotingFormGUI;
use LiveVoting\QuestionTypes\NumberRange\xlvoNumberRangeVotingFormGUI;
use LiveVoting\QuestionTypes\xlvoQuestionTypes;
use LiveVoting\QuestionTypes\xlvoSubFormGUI;
use LiveVoting\Utils\LiveVotingTrait;
use srag\CustomInputGUIs\LiveVoting\TextAreaInputGUI\TextAreaInputGUI;
use srag\CustomInputGUIs\LiveVoting\TextInputGUI\TextInputGUI;
use srag\DIC\LiveVoting\DICTrait;
use xlvoVotingGUI;

class xlvoVotingFormGUI extends ilPropertyFormGUI
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    public const F_COLUMNS = 'columns';
    public const USE_F_COLUMNS = true;
    private xlvoVoting $voting;
    protected xlvoVotingGUI $parent_gui;
    protected bool $is_new;
    protected int $voting_type;
    protected int $voting_id;

    /**
     * @param xlvoVotingGUI $parent_gui
     * @param xlvoVoting    $xlvoVoting
     */
    public function __construct(xlvoVotingGUI $parent_gui, xlvoVoting $xlvoVoting)
    {
        parent::__construct();

        $this->voting = $xlvoVoting;
        $this->parent_gui = $parent_gui;
        $this->is_new = ($this->voting->getId() === 0);

        $this->initForm();
    }

    protected function initForm(): void
    {
        if ($this->is_new) {
            $h = new ilHiddenInputGUI('type');
            $this->addItem($h);
        }

        $this->setTarget('_top');
        $this->setFormAction(self::dic()->ctrl()->getFormAction($this->parent_gui));
        $this->initButtons();

        $te = new ilNonEditableValueGUI($this->parent_gui->txt('type'));
        $te->setValue($this->txt('type_' . $this->voting->getVotingType()));
        $te->setInfo($this->txt('type_' . $this->voting->getVotingType() . "_info"));
        $this->addItem($te);

        $te = new TextInputGUI($this->parent_gui->txt('title'), 'title');
        //		$te->setInfo($this->parent_gui->txt('info_voting_title'));
        $te->setRequired(true);
        $this->addItem($te);

        $ta = new TextAreaInputGUI($this->parent_gui->txt('description'), 'description');
        //		$ta->setInfo($this->parent_gui->txt('info_voting_description'));
        //		$this->addItem($ta);

        $te = new TextAreaInputGUI($this->parent_gui->txt('question'), 'question');
        $te->addPlugin('latex');
        $te->addButton('latex');
        $te->addButton('pastelatex');
        $te->setRequired(true);
        $te->setRTESupport(
            ilObject::_lookupObjId($_GET['ref_id']),
            "dcl",
            ilLiveVotingPlugin::PLUGIN_ID,
            null,
            false
        ); // We have to prepend that this is a datacollection
        $te->setUseRte(true);
        $te->setRteTags([
            'p',
            'a',
            'br',
            'strong',
            'b',
            'i',
            'em',
            'span',
            'img',
        ]);
        $te->usePurifier(true);
        $te->disableButtons([
            'charmap',
            'undo',
            'redo',
            'justifyleft',
            'justifycenter',
            'justifyright',
            'justifyfull',
            'anchor',
            'fullscreen',
            'cut',
            'copy',
            'paste',
            'pastetext',
            'formatselect',
            'bullist',
            'hr',
            'sub',
            'sup',
            'numlist',
            'cite',
        ]);

        $te->setRows(5);
        $this->addItem($te);

        // Columns
        if (static::USE_F_COLUMNS) {
            $columns = new ilSelectInputGUI($this->txt(self::F_COLUMNS), self::F_COLUMNS);
            $columns->setOptions(range(1, 4));
            $this->addItem($columns);
        }

        $xlvoSingleVoteSubFormGUI = xlvoSubFormGUI::getInstance($this->getVoting());
        $xlvoSingleVoteSubFormGUI->appedElementsToForm($this);
        $xlvoSingleVoteSubFormGUI->addJsAndCss(self::dic()->ui()->mainTemplate());
    }

    protected function initButtons(): void
    {
        if ($this->is_new) {
            $this->setTitle($this->parent_gui->txt('form_title_create'));
            $this->addCommandButton(xlvoVotingGUI::CMD_CREATE, $this->parent_gui->txt('create'));
        } else {
            $this->setTitle($this->parent_gui->txt('form_title_update'));
            $this->addCommandButton(xlvoVotingGUI::CMD_UPDATE, $this->parent_gui->txt('update'));
            $this->addCommandButton(xlvoVotingGUI::CMD_UPDATE_AND_STAY, $this->parent_gui->txt('update_and_stay'));
        }

        $this->addCommandButton(xlvoVotingGUI::CMD_CANCEL, $this->parent_gui->txt('cancel'));
    }

    protected function txt(string $key): string
    {
        return $this->parent_gui->txt($key);
    }

    public function getVoting(): xlvoVoting
    {
        return $this->voting;
    }

    public function setVoting(xlvoVoting $voting): void
    {
        $this->voting = $voting;
    }

    public static function get(xlvoVotingGUI $parent_gui, xlvoVoting $xlvoVoting)
    {
        switch ($xlvoVoting->getVotingType()) {
            case xlvoQuestionTypes::TYPE_FREE_INPUT:
                return new xlvoFreeInputVotingFormGUI($parent_gui, $xlvoVoting);

            case xlvoQuestionTypes::TYPE_NUMBER_RANGE:
                return new xlvoNumberRangeVotingFormGUI($parent_gui, $xlvoVoting);

            default:
                return new self($parent_gui, $xlvoVoting);
        }
    }

    public function fillForm(): void
    {
        $array = [
            'title' => $this->voting->getTitle(),
            'description' => $this->voting->getDescription(),
            'question' => $this->voting->getQuestionForEditor(),
            'voting_status' => ($this->voting->getVotingStatus() === xlvoVoting::STAT_ACTIVE)
        ];
        if ($this->is_new) {
            $array['type'] = $this->voting->getVotingType();
            $array['voting_type'] = $this->voting->getVotingType();
        }
        if (static::USE_F_COLUMNS) {
            $array[self::F_COLUMNS] = ($this->voting->getColumns() - 1);
        }

        $array = xlvoSubFormGUI::getInstance($this->getVoting())->appendValues($array);

        $this->setValuesByArray($array);
        if ($this->voting->getVotingStatus() === xlvoVoting::STAT_INCOMPLETE) {
            self::dic()->ui()->mainTemplate()->setOnScreenMessage('info', $this->parent_gui->txt('msg_voting_not_complete'), false);
        }
    }

    /**
     * @throws ilException
     */
    public function saveObject(): bool
    {
        if (!$this->fillObject()) {
            return false;
        }

        if ($this->voting->getObjId() === $this->parent_gui->getObjId()) {
            $this->voting->store();
            xlvoSubFormGUI::getInstance($this->getVoting())->handleAfterCreation($this->voting);
        } else {
            self::dic()->ui()->mainTemplate()->setOnScreenMessage('failure', $this->parent_gui->txt('permission_denied_object'), true);
            self::dic()->ctrl()->redirect($this->parent_gui, xlvoVotingGUI::CMD_STANDARD);
        }

        return true;
    }

    /**
     * @throws ilException
     */
    public function fillObject(): bool
    {
        if (!$this->checkInput()) {
            return false;
        }

        if ($this->is_new) {
            $this->voting->setVotingType($this->getInput('type'));
        }
        $this->voting->setTitle($this->getInput('title'));
        $this->voting->setDescription($this->getInput('description'));
        $this->voting->setQuestion(ilRTE::_replaceMediaObjectImageSrc($this->getInput('question'), 0));
        $this->voting->setObjId($this->parent_gui->getObjId());
        if (static::USE_F_COLUMNS) {
            $this->voting->setColumns((int) $this->getInput(self::F_COLUMNS) + 1);
        }

        try {
            xlvoSubFormGUI::getInstance($this->getVoting())->handleAfterSubmit($this);

            return true;
        } catch (xlvoSubFormGUIHandleFieldException $ex) {
            self::dic()->ui()->mainTemplate()->setOnScreenMessage('failure', $ex->getMessage(), true);

            return false;
        }
    }
}
