<?php

declare(strict_types=1);

namespace LiveVoting\QuestionTypes\FreeInput;

use ilLiveVotingPlugin;
use LiveVoting\Display\Bar\xlvoBarFreeInputsGUI;
use LiveVoting\Display\Bar\xlvoBarGroupingCollectionGUI;
use LiveVoting\Exceptions\xlvoPlayerException;
use LiveVoting\Voting\xlvoVotingManager2;
use srag\DIC\LiveVoting\DICTrait;

class xlvoFreeInputCategoriesGUI
{
    use DICTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    public const TITLE = 'title';
    public const VOTES = 'votes';
    private bool $removable = false;
    protected array $categories = [];

    public function __construct(xlvoVotingManager2 $manager, bool $edit_mode = false)
    {
        $this->setRemovable($edit_mode);
        /** @var xlvoFreeInputCategory $category */
        foreach (
            xlvoFreeInputCategory::where(
                ['voting_id' => $manager->getVoting()->getId(), 'round_id' => $manager->getPlayer()->getRoundId()]
            )
                                 ->get() as $category
        ) {
            $bar_collection = new xlvoBarGroupingCollectionGUI();
            $bar_collection->setRemovable($this->isRemovable());

            $this->categories[$category->getId()] = [
                self::TITLE => $category->getTitle(),
                self::VOTES => $bar_collection
            ];
        }
    }

    public function isRemovable(): bool
    {
        return $this->removable;
    }

    public function setRemovable(bool $removable): void
    {
        $this->removable = $removable;
    }

    /**
     * @throws xlvoPlayerException
     */
    public function addBar(xlvoBarFreeInputsGUI $bar_gui, int $cat_id): void
    {
        $bar_gui->setRemovable($this->isRemovable());
        if (!($this->categories[$cat_id][self::VOTES] instanceof xlvoBarGroupingCollectionGUI)) {
            throw new xlvoPlayerException('category not found', xlvoPlayerException::CATEGORY_NOT_FOUND);
        }
        $this->categories[$cat_id][self::VOTES]->addBar($bar_gui);
    }

    /**
     * @throws \ilTemplateException
     * @throws \srag\DIC\LiveVoting\Exception\DICException
     */
    public function getHTML(): string
    {
        $tpl = self::plugin()->template('default/QuestionTypes/FreeInput/tpl.free_input_categories.html');
        // TODO: xlvoBarGroupingCollection GUI verwenden?
        foreach ($this->categories as $cat_id => $data) {
            $cat_tpl = self::plugin()->template('default/QuestionTypes/FreeInput/tpl.free_input_category.html');
            /** @var xlvoFreeInputCategory $category */
            $cat_tpl->setVariable('ID', $cat_id);
            $cat_tpl->setVariable('TITLE', $data[self::TITLE]);
            if ($this->isRemovable()) {
                $cat_tpl->touchBlock('remove_button');
            }

            $cat_tpl->setVariable('VOTES', $data[self::VOTES]->getHTML());
            $tpl->setCurrentBlock('category');
            $tpl->setVariable('CATEGORY', $cat_tpl->get());
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }
}
