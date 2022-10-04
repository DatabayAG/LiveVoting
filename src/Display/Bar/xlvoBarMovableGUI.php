<?php

declare(strict_types=1);

namespace LiveVoting\Display\Bar;

use ilLiveVotingPlugin;
use ilTemplate;
use LiveVoting\Option\xlvoOption;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

class xlvoBarMovableGUI implements xlvoGeneralBarGUI
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    protected ilTemplate $tpl;
    /** @var xlvoOption[] */
    protected array $options = [];
    protected array $order = [];
    protected int $vote_id;
    protected bool $show_option_letter = false;

    public function __construct(array $options, array $order = [], $vote_id = null)
    {
        $this->options = $options;
        $this->order = $order;
        $this->vote_id = $vote_id;
        $this->tpl = self::plugin()->template('default/Display/Bar/tpl.bar_movable.html', false);
    }

    public function getHTML(): string
    {
        $i = 1;
        $this->tpl->setVariable('VOTE_ID', $this->vote_id);
        if (count($this->order) > 0) {
            $this->tpl->setVariable('YOUR_ORDER', self::plugin()->translate('qtype_4_your_order'));
            foreach ($this->order as $value) {
                $xlvoOption = $this->options[$value];
                if (!$xlvoOption instanceof xlvoOption) {
                    continue;
                }
                $this->tpl->setCurrentBlock('option');
                $this->tpl->setVariable('ID', $xlvoOption->getId());
                if ($this->getShowOptionLetter()) {
                    $this->tpl->setVariable('OPTION_LETTER', $xlvoOption->getCipher());
                }
                $this->tpl->setVariable('OPTION', $xlvoOption->getTextForPresentation());
                $this->tpl->parseCurrentBlock();
                $i++;
            }
        } else {
            foreach ($this->options as $xlvoOption) {
                $this->tpl->setCurrentBlock('option');
                $this->tpl->setVariable('ID', $xlvoOption->getId());
                if ($this->getShowOptionLetter()) {
                    $this->tpl->setVariable('OPTION_LETTER', $xlvoOption->getCipher());
                }
                $this->tpl->setVariable('OPTION', $xlvoOption->getTextForPresentation());
                $this->tpl->parseCurrentBlock();
                $i++;
            }
        }

        return $this->tpl->get();
    }

    public function getShowOptionLetter(): bool|string
    {
        return $this->show_option_letter;
    }

    public function setShowOptionLetter(bool $show_option_letter): void
    {
        $this->show_option_letter = $show_option_letter;
    }
}
