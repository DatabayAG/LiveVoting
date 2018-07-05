<?php

use LiveVoting\Display\Bar\xlvoGeneralBarGUI;
use LiveVoting\Option\xlvoOption;

/**
 * Class xlvoMovableBarGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class xlvoBarMovableGUI implements xlvoGeneralBarGUI {

	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var xlvoOption[]
	 */
	protected $options = array();
	/**
	 * @var array
	 */
	protected $order = array();
	/**
	 * @var int
	 */
	protected $vote_id = NULL;
	/**
	 * @var bool
	 */
	protected $show_option_letter = false;
	/**
	 * @var ilLiveVotingPlugin
	 */
	protected $pl;


	/**
	 * xlvoBarMovableGUI constructor.
	 *
	 * @param array $options
	 * @param array $order
	 */
	public function __construct(array $options, array $order = array(), $vote_id = NULL) {
		$this->options = $options;
		$this->order = $order;
		$this->vote_id = $vote_id;
		$this->pl = ilLiveVotingPlugin::getInstance();
		$this->tpl = $this->pl->getTemplate('default/Display/Bar/tpl.bar_movable.html', false);
	}


	/**
	 * @return string
	 */
	public function getHTML() {
		$i = 1;
		$this->tpl->setVariable('VOTE_ID', $this->vote_id);
		if (count($this->order) > 0) {
			$this->tpl->setVariable('YOUR_ORDER', ilLiveVotingPlugin::getInstance()->txt('qtype_4_your_order'));
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
				$i ++;
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
				$i ++;
			}
		}

		return $this->tpl->get();
	}


	/**
	 * @return string
	 */
	public function getShowOptionLetter() {
		return $this->show_option_letter;
	}


	/**
	 * @param string $show_option_letter
	 */
	public function setShowOptionLetter($show_option_letter) {
		$this->show_option_letter = $show_option_letter;
	}
}
