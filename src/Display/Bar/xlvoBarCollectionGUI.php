<?php

declare(strict_types=1);

namespace LiveVoting\Display\Bar;

use ilLiveVotingPlugin;
use ilTemplate;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

class xlvoBarCollectionGUI
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    protected ilTemplate $tpl;
    protected int $total_votes = 0;
    protected bool $show_total_votes = false;
    protected int $total_voters = 0;
    protected bool $show_total_voters = false;

    public function __construct()
    {
        $this->tpl = self::plugin()->template('default/Display/Bar/tpl.bar_collection.html');
    }

    public function getHTML(): string
    {
        $this->renderVotersAndVotes();

        return $this->tpl->get();
    }

    protected function renderVotersAndVotes(): void
    {
        if ($this->isShowTotalVotes()) {
            $this->tpl->setCurrentBlock('total_votes');
            $this->tpl->setVariable(
                'TOTAL_VOTES',
                self::plugin()->translate('qtype_1_total_votes') . ': ' . $this->getTotalVotes()
            );
            $this->tpl->parseCurrentBlock();
        }
        if ($this->isShowTotalVoters()) {
            $this->tpl->setCurrentBlock('total_voters');
            $this->tpl->setVariable(
                'TOTAL_VOTERS',
                self::plugin()->translate('qtype_1_total_voters') . ': ' . $this->getTotalVoters()
            );
            $this->tpl->parseCurrentBlock();
        }
    }

    public function isShowTotalVotes(): bool
    {
        return $this->show_total_votes;
    }

    public function setShowTotalVotes(bool $show_total_votes): void
    {
        $this->show_total_votes = $show_total_votes;
    }

    public function getTotalVotes(): int
    {
        return $this->total_votes;
    }

    public function setTotalVotes(int $total_votes): void
    {
        $this->total_votes = $total_votes;
    }

    public function isShowTotalVoters(): bool
    {
        return $this->show_total_voters;
    }

    public function setShowTotalVoters(bool $show_total_voters): void
    {
        $this->show_total_voters = $show_total_voters;
    }

    public function getTotalVoters(): int
    {
        return $this->total_voters;
    }

    public function setTotalVoters(int $total_voters): void
    {
        $this->total_voters = $total_voters;
    }

    public function addBar(xlvoGeneralBarGUI $bar_gui): void
    {
        $this->tpl->setCurrentBlock('bar');
        $this->tpl->setVariable('BAR', $bar_gui->getHTML());
        $this->tpl->parseCurrentBlock();
    }

    public function addSolution(string $html): void
    {
        $this->tpl->setCurrentBlock('solution');
        $this->tpl->setVariable('SOLUTION', $html);
        $this->tpl->parseCurrentBlock();
    }
}
