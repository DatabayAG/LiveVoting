<?php

declare(strict_types=1);

namespace LiveVoting\Voting;

use ilLiveVotingPlugin;
use ilRepositorySelector2InputGUI;
use LiveVoting\Utils\LiveVotingTrait;
use srag\CustomInputGUIs\LiveVoting\PropertyFormGUI\PropertyFormGUI;
use xlvoVotingGUI;

class DuplicateToAnotherObjectSelectFormGUI extends PropertyFormGUI
{
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    public const LANG_MODULE = "voting";

    public function __construct(xlvoVotingGUI $parent)
    {
        parent::__construct($parent);
    }

    protected function getValue(string $key): void
    {
        switch ($key) {
            default:
        }
    }

    protected function initCommands(): void
    {
        $this->addCommandButton(xlvoVotingGUI::CMD_DUPLICATE_TO_ANOTHER_OBJECT, $this->txt("duplicate"));
    }

    protected function initFields(): void
    {
        $this->addItem($this->getRepositorySelector());
    }

    public function getRepositorySelector(): ilRepositorySelector2InputGUI
    {
        $repository_selector = new ilRepositorySelector2InputGUI(self::plugin()->translate("obj_xlvo"), "ref_id");

        $repository_selector->setRequired(true);

        $repository_selector->getExplorerGUI()->setSelectableTypes([ilLiveVotingPlugin::PLUGIN_ID]);

        return $repository_selector;
    }

    protected function initId(): void
    {
    }

    protected function initTitle(): void
    {
        $this->setTitle($this->txt(xlvoVotingGUI::CMD_DUPLICATE_TO_ANOTHER_OBJECT));
    }

    protected function storeValue(string $key, $value): void
    {
        switch ($key) {
            default:
                break;
        }
    }
}
