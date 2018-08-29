<?php

require_once __DIR__ . '/../vendor/autoload.php';

use LiveVoting\Conf\xlvoConf;
use LiveVoting\Conf\xlvoConfOld;
use LiveVoting\Option\xlvoData;
use LiveVoting\Option\xlvoOption;
use LiveVoting\Option\xlvoOptionOld;
use LiveVoting\Player\xlvoPlayer;
use LiveVoting\Round\xlvoRound;
use LiveVoting\User\xlvoVoteHistoryObject;
use LiveVoting\Vote\xlvoVote;
use LiveVoting\Vote\xlvoVoteOld;
use LiveVoting\Voter\xlvoVoter;
use LiveVoting\Voting\xlvoVoting;
use LiveVoting\Voting\xlvoVotingConfig;
use srag\DIC\DICTrait;

/**
 * LiveVoting repository object plugin
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id$
 *
 */
class ilLiveVotingPlugin extends ilRepositoryObjectPlugin {

	use DICTrait;
	const PLUGIN_ID = 'xlvo';
	const PLUGIN_NAME = 'LiveVoting';
	const PLUGIN_CLASS_NAME = self::class;
	const KEY_UNINSTALL_REMOVE_DATA = "uninstall_remove_data";
	/**
	 * @var ilLiveVotingPlugin
	 */
	protected static $instance;


	/**
	 * @return ilLiveVotingPlugin
	 */
	public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 *
	 */
	public function __construct() {
		parent::__construct();
	}


	/**
	 * @return string
	 */
	public function getPluginName() {
		return self::PLUGIN_NAME;
	}


	/**
	 * @return bool
	 */
	protected function beforeUninstallCustom() {
		$uninstall_remove_data = xlvoConf::getConfig(self::KEY_UNINSTALL_REMOVE_DATA);

		if ($uninstall_remove_data === NULL) {
			LiveVotingRemoveDataConfirm::saveParameterByClass();

			self::dic()->ctrl()->redirectByClass([
				ilUIPluginRouterGUI::class,
				LiveVotingRemoveDataConfirm::class
			], LiveVotingRemoveDataConfirm::CMD_CONFIRM_REMOVE_DATA);

			return false;
		}

		$uninstall_remove_data = boolval($uninstall_remove_data);

		if ($uninstall_remove_data) {
			$this->removeData();
		} else {
			// Ask again if reinstalled
			xlvoConf::remove(self::KEY_UNINSTALL_REMOVE_DATA);
		}

		return true;
	}


	/**
	 *
	 */
	protected function removeData() {
		self::dic()->database()->dropTable(xlvoConfOld::TABLE_NAME, false);
		self::dic()->database()->dropTable(xlvoVotingConfig::TABLE_NAME, false);
		self::dic()->database()->dropTable(xlvoData::TABLE_NAME, false);
		self::dic()->database()->dropTable(xlvoOption::TABLE_NAME, false);
		self::dic()->database()->dropTable(xlvoOptionOld::TABLE_NAME, false);
		self::dic()->database()->dropTable(xlvoPlayer::TABLE_NAME, false);
		self::dic()->database()->dropTable(xlvoRound::TABLE_NAME, false);
		self::dic()->database()->dropTable(xlvoVote::TABLE_NAME, false);
		self::dic()->database()->dropTable(xlvoVoteOld::TABLE_NAME, false);
		self::dic()->database()->dropTable(xlvoVoteHistoryObject::TABLE_NAME, false);
		self::dic()->database()->dropTable(xlvoVoting::TABLE_NAME, false);
		self::dic()->database()->dropTable(xlvoConf::TABLE_NAME, false);
		self::dic()->database()->dropTable(xlvoVoter::TABLE_NAME, false);
	}


	/**
	 *
	 */
	protected function uninstallCustom() {

	}


	//		/**
	//		 * @param string $key
	//		 * @return mixed|string
	//		 * @throws ilException
	//		 */
	//		public function txt($key) {
	//			require_once 'Customizing/global/plugins/Libraries/PluginTranslator/class.sragPluginTranslator.php;
	//
	//			return sragPluginTranslator::getInstance($this)->active()->write()->txt($key);
	//		}
}
