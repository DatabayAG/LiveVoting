<?php

declare(strict_types=1);

namespace LiveVoting\Voting;

use ilAdvancedSelectionListGUI;
use ilCheckboxInputGUI;
use ilFormPropertyGUI;
use ilLiveVotingPlugin;
use ilObjLiveVotingAccess;
use ilSelectInputGUI;
use ilTable2GUI;
use LiveVoting\Js\xlvoJs;
use LiveVoting\QuestionTypes\xlvoQuestionTypes;
use LiveVoting\Utils\LiveVotingTrait;
use srag\CustomInputGUIs\LiveVoting\TextInputGUI\TextInputGUI;
use srag\DIC\LiveVoting\DICTrait;
use xlvoVotingGUI;
use ilLegacyFormElementsUtil;

class xlvoVotingTableGUI extends ilTable2GUI
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    public const TBL_ID = 'tbl_xlvo';
    public const LENGTH = 100;
    protected xlvoVotingGUI $voting_gui;
    protected array $filter = [];
    protected ilObjLiveVotingAccess $access;

    public function __construct(xlvoVotingGUI $a_parent_obj, $a_parent_cmd)
    {
        $this->voting_gui = $a_parent_obj;
        $this->access = new ilObjLiveVotingAccess();

        xlvoJs::getInstance()->addLibToHeader('sortable.min.js');

        $this->setId(self::TBL_ID);
        $this->setPrefix(self::TBL_ID);
        $this->setFormName(self::TBL_ID);
        self::dic()->ctrl()->saveParameter($a_parent_obj, $this->getNavParameter());

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setRowTemplate('tpl.tbl_voting.html', self::plugin()->directory());
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->initColums();
        $this->addFilterItems();
        $this->parseData();

        $this->setFormAction(self::dic()->ctrl()->getFormAction($a_parent_obj));
        $this->addCommandButton('saveSorting', $this->txt('save_sorting'));
    }

    protected function initColums(): void
    {
        $this->addColumn('', 'position', '20px');
        $this->addColumn($this->txt('title'));
        $this->addColumn($this->txt('question'));
        $this->addColumn($this->txt('type'));
        //		$this->addColumn($this->txt('status'));
        $this->addColumn($this->txt('actions'), '', '150px');
    }

    protected function txt(string $key): string
    {
        return $this->voting_gui->txt($key);
    }

    protected function addFilterItems(): void
    {
        $title = new TextInputGUI($this->txt('title'), 'title');
        $this->addAndReadFilterItem($title);

        $question = new TextInputGUI($this->txt('question'), 'question');
        $this->addAndReadFilterItem($question);

        $status = new ilSelectInputGUI($this->txt('status'), 'voting_status');
        $status_options = array(
            -1 => '',
            xlvoVoting::STAT_INACTIVE => $this->txt('status_' . xlvoVoting::STAT_INACTIVE),
            xlvoVoting::STAT_ACTIVE => $this->txt('status_' . xlvoVoting::STAT_ACTIVE),
            xlvoVoting::STAT_INCOMPLETE => $this->txt('status_' . xlvoVoting::STAT_INCOMPLETE),
        );
        $status->setOptions($status_options);
        //		$this->addAndReadFilterItem($status); deativated at the moment

        $type = new ilSelectInputGUI($this->txt('type'), 'voting_type');
        $type_options = array(
            -1 => '',
        );

        foreach (xlvoQuestionTypes::getActiveTypes() as $qtype) {
            $type_options[$qtype] = $this->txt('type_' . $qtype);
        }

        $type->setOptions($type_options);
        $this->addAndReadFilterItem($type);
    }

    protected function addAndReadFilterItem(ilFormPropertyGUI $item): void
    {
        $this->addFilterItem($item);
        $item->readFromSession();
        if ($item instanceof ilCheckboxInputGUI) {
            $this->filter[$item->getPostVar()] = $item->getChecked();
        } else {
            $this->filter[$item->getPostVar()] = $item->getValue();
        }
    }

    protected function parseData(): void
    {
        // Filtern
        $this->determineOffsetAndOrder();
        $this->determineLimit();

        $collection = xlvoVoting::where(['obj_id' => $this->voting_gui->getObjId()])
                                ->where(['voting_type' => xlvoQuestionTypes::getActiveTypes()])->orderBy(
                                    'position',
                                    'ASC'
                                );
        $this->setMaxCount($collection->count());
        $sorting_column = $this->getOrderField() ?: 'position';
        $offset = $this->getOffset() ?: 0;

        $sorting_direction = $this->getOrderDirection();
        $num = $this->getLimit();

        $collection->orderBy($sorting_column, $sorting_direction);
        $collection->limit($offset, $num);

        foreach ($this->filter as $filter_key => $filter_value) {
            switch ($filter_key) {
                case 'title':
                case 'question':
                    if ($filter_value) {
                        $collection = $collection->where([$filter_key => '%' . $filter_value . '%'], 'LIKE');
                    }
                    break;
                case 'voting_status':

                case 'voting_type':
                    if ($filter_value > -1) {
                        $collection = $collection->where([$filter_key => $filter_value]);
                    }
                    break;
            }
        }
        $this->setData($collection->getArray());
    }

    public function fillRow(array $a_set): void
    {
        /**
         * @var xlvoVoting $xlvoVoting
         */
        $xlvoVoting = xlvoVoting::find($a_set['id']);
        $this->tpl->setVariable('TITLE', $this->shorten($xlvoVoting->getTitle()));
        $this->tpl->setVariable('DESCRIPTION', $this->shorten($xlvoVoting->getDescription()));

        $question = strip_tags($xlvoVoting->getQuestion());

        $question = $this->shorten($question);
        $this->tpl->setVariable('QUESTION', ilLegacyFormElementsUtil::prepareTextareaOutput($question, true));
        $this->tpl->setVariable('TYPE', $this->txt('type_' . $xlvoVoting->getVotingType()));

        $voting_status = $this->getVotingStatus($xlvoVoting->getVotingStatus());
        //		$this->tpl->setVariable('STATUS', $voting_status); // deactivated at the moment

        $this->tpl->setVariable('ID', $xlvoVoting->getId());

        $this->addActionMenu($xlvoVoting);
    }

    protected function shorten(string $question): string
    {
        return strlen($question) > self::LENGTH ? substr($question, 0, self::LENGTH) . "..." : $question;
    }

    protected function getVotingStatus($voting_status): string
    {
        return $this->txt('status_' . $voting_status);
    }

    protected function addActionMenu(xlvoVoting $xlvoVoting): void
    {
        $current_selection_list = new ilAdvancedSelectionListGUI();
        $current_selection_list->setListTitle($this->txt('actions'));
        $current_selection_list->setId('xlvo_actions_' . $xlvoVoting->getId());
        $current_selection_list->setUseImages(false);

        self::dic()->ctrl()->setParameter($this->voting_gui, xlvoVotingGUI::IDENTIFIER, $xlvoVoting->getId());
        if (ilObjLiveVotingAccess::hasWriteAccess()) {
            $current_selection_list->addItem(
                $this->txt('edit'),
                xlvoVotingGUI::CMD_EDIT,
                self::dic()->ctrl()
                    ->getLinkTarget(
                        $this->voting_gui,
                        xlvoVotingGUI::CMD_EDIT
                    )
            );
            $current_selection_list->addItem(
                $this->txt('reset'),
                xlvoVotingGUI::CMD_CONFIRM_RESET,
                self::dic()->ctrl()
                    ->getLinkTarget(
                        $this->voting_gui,
                        xlvoVotingGUI::CMD_CONFIRM_RESET
                    )
            );
            $current_selection_list->addItem(
                $this->txt(xlvoVotingGUI::CMD_DUPLICATE),
                xlvoVotingGUI::CMD_DUPLICATE,
                self::dic()->ctrl()
                    ->getLinkTarget(
                        $this->voting_gui,
                        xlvoVotingGUI::CMD_DUPLICATE
                    )
            );
            $current_selection_list->addItem(
                $this->txt(xlvoVotingGUI::CMD_DUPLICATE_TO_ANOTHER_OBJECT),
                xlvoVotingGUI::CMD_DUPLICATE_TO_ANOTHER_OBJECT_SELECT,
                self::dic()->ctrl()
                    ->getLinkTarget(
                        $this->voting_gui,
                        xlvoVotingGUI::CMD_DUPLICATE_TO_ANOTHER_OBJECT_SELECT
                    )
            );
            $current_selection_list->addItem(
                $this->txt('delete'),
                xlvoVotingGUI::CMD_CONFIRM_DELETE,
                self::dic()->ctrl()
                    ->getLinkTarget(
                        $this->voting_gui,
                        xlvoVotingGUI::CMD_CONFIRM_DELETE
                    )
            );
        }
        $current_selection_list->getHTML();
        $this->tpl->setVariable('ACTIONS', $current_selection_list->getHTML());
    }
}
