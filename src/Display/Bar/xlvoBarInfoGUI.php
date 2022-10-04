<?php

declare(strict_types=1);

namespace LiveVoting\Display\Bar;

class xlvoBarInfoGUI extends xlvoAbstractBarGUI implements xlvoGeneralBarGUI
{
    protected string $value;
    protected string $label;

    public function __construct(string $label, string $value)
    {
        parent::__construct();
        $this->label = $label;
        $this->value = $value;
    }

    public function getHTML(): string
    {
        $this->render();

        return $this->tpl->get();
    }

    protected function render(): void
    {
        parent::render();
        $this->tpl->setVariable('FREE_INPUT', $this->label . ": " . $this->value);
    }
}
