<?php

declare(strict_types=1);

namespace LiveVoting\Display\Bar;

use ilLiveVotingPlugin;
use ilTemplate;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

class xlvoBarPercentageGUI implements xlvoGeneralBarGUI
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    protected int $votes = 0;
    protected int $max_votes = 100;
    protected string $option_letter = '';
    protected ilTemplate $tpl;
    protected string $title = '';
    protected bool $show_in_percent = false;
    protected int $round = 2;

    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getHTML(): string
    {
        $tpl = self::plugin()->template('default/Display/Bar/tpl.bar_percentage.html');

        $tpl->setVariable('TITLE', $this->getTitle());

        $tpl->setVariable('ID', uniqid('', true));
        $tpl->setVariable('TITLE', $this->getTitle());

        if ($this->getOptionLetter()) {
            $tpl->setCurrentBlock('option_letter');
            $tpl->setVariable('OPTION_LETTER', $this->getOptionLetter());
            $tpl->parseCurrentBlock();
        }

        if ($this->getMaxVotes() === 0) {
            $calculated_percentage = 0;
        } else {
            $calculated_percentage = $this->getVotes() / $this->getMaxVotes() * 100;
        }

        $tpl->setVariable('MAX', $this->getMaxVotes());
        $tpl->setVariable('PERCENT', $this->getVotes());
        $tpl->setVariable('PERCENT_STYLE', str_replace(',', '.', (string) round($calculated_percentage, 1)));
        if ($this->isShowInPercent()) {
            $tpl->setVariable('PERCENT_TEXT', round($calculated_percentage, $this->getRound()) . ' %');
        } else {
            $tpl->setVariable('PERCENT_TEXT', round($this->getVotes(), $this->getRound()));
        }

        return $tpl->get();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getOptionLetter(): string
    {
        return $this->option_letter;
    }

    public function setOptionLetter(string $option_letter): void
    {
        $this->option_letter = $option_letter;
    }

    public function getMaxVotes(): int
    {
        return $this->max_votes;
    }

    public function setMaxVotes(int $max_votes): void
    {
        $this->max_votes = $max_votes;
    }

    public function getVotes(): int
    {
        return $this->votes;
    }

    public function setVotes(int $votes): void
    {
        $this->votes = $votes;
    }

    public function isShowInPercent(): bool
    {
        return $this->show_in_percent;
    }

    public function setShowInPercent(bool $show_in_percent): void
    {
        $this->show_in_percent = $show_in_percent;
    }

    public function getRound(): int
    {
        return $this->round;
    }

    public function setRound(int $round): void
    {
        $this->round = $round;
    }

    public function getTpl(): ilTemplate
    {
        return $this->tpl;
    }

    public function setTpl(ilTemplate $tpl): void
    {
        $this->tpl = $tpl;
    }
}
