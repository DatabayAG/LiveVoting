<?php

declare(strict_types=1);

namespace LiveVoting\Display\Bar;

use LiveVoting\Vote\xlvoVote;
use LiveVoting\Voting\xlvoVoting;

class xlvoBarFreeInputsGUI extends xlvoAbstractBarGUI implements xlvoGeneralBarGUI
{
    private int $occurrences;
    private bool $removable = false;
    private bool $strong = false;
    private bool $center = false;
    private bool $big = false;
    protected xlvoVoting $voting;
    protected xlvoVote $vote;

    public function __construct(xlvoVoting $voting, xlvoVote $vote)
    {
        parent::__construct();
        $this->voting = $voting;
        $this->vote = $vote;
        $this->tpl = self::plugin()->template('default/Display/Bar/tpl.bar_free_input.html');
        $this->occurrences = 0;
    }

    public function getHTML(): string
    {
        $this->render();

        return $this->tpl->get();
    }

    /**
     *
     */
    protected function render(): void
    {
        $this->tpl->setVariable('FREE_INPUT', nl2br($this->vote->getFreeInput(), false));
        $this->tpl->setVariable('ID', $this->vote->getId());

        if ($this->isRemovable()) {
            $this->tpl->touchBlock('remove_button');
        }
        if ($this->isCenter()) {
            $this->tpl->touchBlock('center');
        }
        if ($this->isBig()) {
            $this->tpl->touchBlock('big');
        }
        if ($this->isStrong()) {
            $this->tpl->touchBlock('strong');
            $this->tpl->touchBlock('strong_end');
        }

        if ($this->occurrences > 1) {
            $this->tpl->setVariable('GROUPED_BARS_COUNT', $this->occurrences);
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

    public function isCenter(): bool
    {
        return $this->center;
    }

    public function setCenter(bool $center): void
    {
        $this->center = $center;
    }

    public function isBig(): bool
    {
        return $this->big;
    }

    public function setBig(bool $big): void
    {
        $this->big = $big;
    }

    public function isStrong(): bool
    {
        return $this->strong;
    }

    public function setStrong(bool $strong): void
    {
        $this->strong = $strong;
    }

    public function getOccurrences(): int
    {
        return $this->occurrences;
    }

    public function setOccurrences(int $occurrences): void
    {
        $this->occurrences = $occurrences;
    }

    public function equals(xlvoGeneralBarGUI $bar): bool
    {
        return strcasecmp(nl2br($this->vote->getFreeInput(), false), nl2br($bar->vote->getFreeInput(), false)) === 0;
    }
}
