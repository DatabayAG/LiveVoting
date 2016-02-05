<?php

require_once('./Services/ActiveRecord/class.ActiveRecord.php');

/**
 * Class xlvoVoting
 *
 * @author  Daniel Aemmer <daniel.aemmer@phbern.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class xlvoVoting extends ActiveRecord {

	const STAT_ACTIVE = 5;
	const STAT_INACTIVE = 1;
	const STAT_INCOMPLETE = 2;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           8
	 * @db_is_primary       true
	 * @con_sequence        true
	 */
	protected $id;
	/**
	 * @var bool
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           1
	 */
	protected $multi_selection;
	/**
	 * @var bool
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           1
	 */
	protected $colors;
	/**
	 * @var bool
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           1
	 */
	protected $multi_free_input;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           8
	 */
	protected $obj_id;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           256
	 */
	protected $title;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           4000
	 */
	protected $description;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           4000
	 */
	protected $question;
	/**
	 * @var string
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           8
	 */
	protected $voting_type;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           8
	 */
	protected $voting_status = self::STAT_ACTIVE;
	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_fieldtype        integer
	 * @db_length           8
	 */
	protected $position;
	/**
	 * @var xlvoOption[]
	 */
	protected $voting_options;
	/**
	 * @var xlvoOption
	 */
	protected $first_voting_option;


	/**
	 * @return bool
	 */
	public function isFirst() {
		/**
		 * @var $first xlvoVoting
		 */
		$first = self::where(array( 'obj_id' => $this->getObjId() ))->orderBy('position', 'ASC')->first();

		return $first->getId() == $this->getId();
	}


	/**
	 * @return bool
	 */
	public function isLast() {
		/**
		 * @var $first xlvoVoting
		 */
		$first = self::where(array( 'obj_id' => $this->getObjId() ))->orderBy('position', 'DESC')->first();

		return $first->getId() == $this->getId();
	}


	/**
	 * @return string
	 */
	public static function returnDbTableName() {
		return 'rep_robj_xlvo_voting_n';
	}


	/**
	 * @return boolean
	 */
	public function isMultiSelection() {
		return $this->multi_selection;
	}


	/**
	 * @param boolean $multi_selection
	 */
	public function setMultiSelection($multi_selection) {
		$this->multi_selection = $multi_selection;
	}


	/**
	 * @return boolean
	 */
	public function isColors() {
		return $this->colors;
	}


	/**
	 * @param boolean $colors
	 */
	public function setColors($colors) {
		$this->colors = $colors;
	}


	/**
	 * @return boolean
	 */
	public function isMultiFreeInput() {
		return $this->multi_free_input;
	}


	/**
	 * @param boolean $multi_free_input
	 */
	public function setMultiFreeInput($multi_free_input) {
		$this->multi_free_input = $multi_free_input;
	}


	public function afterObjectLoad() {
		/**
		 * @var xlvoOption[] $xlvoOptions
		 * @var xlvoOption $first_voting_option
		 */
		$xlvoOptions = xlvoOption::where(array( 'voting_id' => $this->id ))->orderBy('position')->get();
		$this->setVotingOptions($xlvoOptions);
		$first_voting_option = xlvoOption::where(array( 'voting_id' => $this->id ))->orderBy('position')->first();
		$this->setFirstVotingOption($first_voting_option);
	}


	public function store() {
		if (!xlvoVoting::where(array( 'id' => $this->getId() ))->hasSets()) {
			$this->create();
		} else {
			$this->update();
		}
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getObjId() {
		return $this->obj_id;
	}


	/**
	 * @param int $obj_id
	 */
	public function setObjId($obj_id) {
		$this->obj_id = $obj_id;
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}


	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}


	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}


	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}


	/**
	 * @return string
	 */
	public function getQuestion() {
		return $this->question;
	}


	/**
	 * @param string $question
	 */
	public function setQuestion($question) {
		$this->question = $question;
	}


	/**
	 * @return string
	 */
	public function getVotingType() {
		return $this->voting_type;
	}


	/**
	 * @param string $voting_type
	 */
	public function setVotingType($voting_type) {
		$this->voting_type = $voting_type;
	}


	/**
	 * @return int
	 */
	public function getVotingStatus() {
		return $this->voting_status;
	}


	/**
	 * @param int $voting_status
	 */
	public function setVotingStatus($voting_status) {
		$this->voting_status = $voting_status;
	}


	/**
	 * @return int
	 */
	public function getPosition() {
		return $this->position;
	}


	/**
	 * @param int $position
	 */
	public function setPosition($position) {
		$this->position = $position;
	}


	/**
	 * @return xlvoOption[]
	 */
	public function getVotingOptions() {
		return $this->voting_options;
	}


	/**
	 * @param xlvoOption[] $voting_options
	 */
	public function setVotingOptions($voting_options) {
		$this->voting_options = $voting_options;
	}


	/**
	 * @return xlvoOption
	 */
	public function getFirstVotingOption() {
		return $this->first_voting_option;
	}


	/**
	 * @param xlvoOption $first_voting_option
	 */
	public function setFirstVotingOption($first_voting_option) {
		$this->first_voting_option = $first_voting_option;
	}
}