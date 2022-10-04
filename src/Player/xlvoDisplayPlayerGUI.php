<?php

declare(strict_types=1);

namespace LiveVoting\Player;

use ilException;
use ilLiveVotingPlugin;
use ilTemplate;
use LiveVoting\Exceptions\xlvoVotingManagerException;
use LiveVoting\Option\xlvoOption;
use LiveVoting\Pin\xlvoPin;
use LiveVoting\QuestionTypes\xlvoInputResultsGUI;
use LiveVoting\QuestionTypes\xlvoQuestionTypes;
use LiveVoting\Utils\LiveVotingTrait;
use LiveVoting\Voter\xlvoVoter;
use LiveVoting\Voting\xlvoVoting;
use LiveVoting\Voting\xlvoVotingConfig;
use LiveVoting\Voting\xlvoVotingManager2;
use srag\DIC\LiveVoting\DICTrait;

class xlvoDisplayPlayerGUI
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    protected ilTemplate $tpl;
    protected xlvoVoting $voting;
    protected int $answer_count = 64;
    protected xlvoVotingManager2 $manager;

    public function __construct(xlvoVotingManager2 $manager)
    {
        $this->manager = $manager;
        $this->tpl = self::plugin()->template('default/Player/tpl.player.html');
    }

    public function getHTML(bool $inner = false): string
    {
        $this->render();
        $open = '<div id="xlvo-display-player" class="display-player panel panel-primary">';
        $close = '</div>';

        if ($inner) {
            return $this->tpl->get();
        }

        return $open . $this->tpl->get() . $close;
    }

    /**
     * @throws ilException
     */
    protected function render(): void
    {
        /**
         * @var xlvoVotingConfig $config
         */
        $config = $this->manager->getVotingConfig();
        /**
         * @var xlvoPlayer $player
         */
        $player = $this->manager->getPlayer();

        $xlvoInputResultGUI = xlvoInputResultsGUI::getInstance($this->manager);
        if ($player->isShowResults()) {
            //add result view to player
            $this->tpl->setVariable('OPTION_CONTENT', $xlvoInputResultGUI->getHTML());
        } else {
            //add options to player
            $xlvoOptions = $this->manager->getVoting()->getVotingOptions();
            /*usort($xlvoOptions, function (xlvoOption $option1, xlvoOption $option2) {
                return ($option1->getPosition() - $option2->getPosition());
            });*/

            foreach ($xlvoOptions as $item) {
                $this->addOption($item);
            }
        }

        $this->tpl->setVariable('VOTING_ID', $this->manager->getVoting()->getId());
        $this->tpl->setVariable('TITLE', $this->manager->getVoting()->getTitle());
        $this->tpl->setVariable('QUESTION', $this->manager->getVoting()->getQuestionForPresentation());
        $this->tpl->setVariable('VOTING_ID', $this->manager->getVoting()->getId());
        $this->tpl->setVariable('OBJ_ID', $this->manager->getVoting()->getObjId());
        $this->tpl->setVariable('FROZEN', $player->isFrozen());
        $this->tpl->setVariable('PIN', xlvoPin::formatPin($config->getPin()));
        if ($this->manager->getVotingConfig()->isShowAttendees()) {
            $this->tpl->setCurrentBlock('attendees');
            $this->tpl->setVariable(
                'ONLINE_TEXT',
                self::plugin()->translate("start_online", "", [
                xlvoVoter::countVoters($this->manager->getPlayer()->getId())
            ])
            );
            $this->tpl->parseCurrentBlock();
        }
        if ($this->manager->getPlayer()->isCountDownRunning()) {
            $this->tpl->setCurrentBlock('countdown');
            $cd = $this->manager->getPlayer()->remainingCountDown();
            $this->tpl->setVariable('COUNTDOWN', $cd . ' ' . self::plugin()->translate('player_seconds'));
            $this->tpl->setVariable('COUNTDOWN_CSS', $this->manager->getPlayer()->getCountdownClassname());
            $this->tpl->parseCurrentBlock();
        }

        //parse votes block
        $this->tpl->setVariable(
            'VOTERS_TEXT',
            self::plugin()->translate('player_voters_description', "", [$this->manager->countVoters()])
        );

        $this->tpl->setVariable('COUNT', $this->manager->countVotings());
        $this->tpl->setVariable('POSITION', $this->manager->getVotingPosition());
    }

    protected function addOption(xlvoOption $option): void
    {
        if ($option->getType() === xlvoQuestionTypes::TYPE_FREE_INPUT) {
            return;
        }

        //workaround due to the old question design.
        // TODO: Move to xlvoNumberRangeResultsGUI
        if ($option->getType() === xlvoQuestionTypes::TYPE_NUMBER_RANGE) {
            $columnWith = 6; //because of bootstrap grid 12 = 100%, 6 = 50% therefore 2 columns
            $percentage = $this->manager->getVoting()->getPercentage() === 1 ? ' %' : '';

            $this->tpl->setCurrentBlock('option2');
            $this->tpl->setVariable('OPTION_LETTER', self::plugin()->translate('qtype_6_range_start'));
            $this->tpl->setVariable('OPTION_COL', $columnWith);
            $this->tpl->setVariable('OPTION_TEXT', "{$this->manager->getVoting()->getStartRange()}$percentage");
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock('option2');
            $this->tpl->setVariable('OPTION_LETTER', self::plugin()->translate('qtype_6_range_end'));
            $this->tpl->setVariable('OPTION_COL', $columnWith);
            $this->tpl->setVariable('OPTION_TEXT', "{$this->manager->getVoting()->getEndRange()}$percentage");
            $this->tpl->parseCurrentBlock();

            return;
        }

        $this->answer_count++;
        $this->tpl->setCurrentBlock('option');
        $this->tpl->setVariable('OPTION_LETTER', $option->getCipher());
        $this->tpl->setVariable('OPTION_COL', $this->manager->getVoting()->getComputedColums());
        $this->tpl->setVariable('OPTION_TEXT', $option->getTextForPresentation());
        $this->tpl->parseCurrentBlock();
    }
}
