<?php

declare(strict_types=1);

namespace LiveVoting\Display\Bar;

use ilLiveVotingPlugin;
use ilTemplate;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

abstract class xlvoAbstractBarGUI implements xlvoGeneralBarGUI
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    private bool $strong = false;
    private bool $center = false;
    private bool $big = false;
    private bool $dark = false;
    protected ilTemplate $tpl;

    public function __construct()
    {
    }

    public function getHTML(): string
    {
        $this->render();

        return $this->tpl->get();
    }

    protected function render(): void
    {
        $this->initTemplate();
        if ($this->isCenter()) {
            $this->tpl->touchBlock('center');
        }
        if ($this->isBig()) {
            $this->tpl->touchBlock('big');
        }
        if ($this->isDark()) {
            $this->tpl->touchBlock('dark');
        }
        if ($this->isStrong()) {
            $this->tpl->touchBlock('strong');
            $this->tpl->touchBlock('strong_end');
        }
    }

    protected function initTemplate(): void
    {
        $this->tpl = self::plugin()->template('default/Display/Bar/tpl.bar_free_input.html');
        self::dic()->ui()->mainTemplate()->addCss(
            self::plugin()->directory() . "/templates/default/Display/Bar/bar.css"
        );
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

    public function isDark(): bool
    {
        return $this->dark;
    }

    public function setDark(bool $dark): void
    {
        $this->dark = $dark;
    }

    public function isStrong(): bool
    {
        return $this->strong;
    }

    public function setStrong(bool $strong): void
    {
        $this->strong = $strong;
    }
}
