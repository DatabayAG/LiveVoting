<?php

declare(strict_types=1);

namespace LiveVoting\Voting;

use ActiveRecordList;
use arException;
use Exception;
use ilObjectTypeMismatchException;
use ilRTE;
use ilUtil;
use LiveVoting\Cache\CachingActiveRecord;
use LiveVoting\Option\xlvoOption;
use LiveVoting\QuestionTypes\FreeInput\xlvoFreeInputSubFormGUI;
use LiveVoting\QuestionTypes\NumberRange\xlvoNumberRangeSubFormGUI;
use LiveVoting\QuestionTypes\xlvoQuestionTypes;
use stdClass;
use ilLegacyFormElementsUtil;

/**
 * Class xlvoVoting
 *
 * @package LiveVoting\Voting
 * @author  Daniel Aemmer <daniel.aemmer@phbern.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class xlvoVoting extends CachingActiveRecord
{
    public const STAT_ACTIVE = 5;
    public const STAT_INACTIVE = 1;
    public const STAT_INCOMPLETE = 2;
    public const ROWS_DEFAULT = 1;
    public const TABLE_NAME = 'rep_robj_xlvo_voting_n';
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     * @db_is_primary       true
     * @con_sequence        true
     */
    protected int $id;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $multi_selection = false;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $colors = false;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $multi_free_input = false;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $obj_id = 0;
    /**
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           256
     */
    protected string $title = '';
    /**
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           4000
     */
    protected string $description = '';
    /**
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           4000
     */
    protected string $question = '';
    /**
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected $voting_type = xlvoQuestionTypes::TYPE_SINGLE_VOTE;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $voting_status = self::STAT_ACTIVE;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $position = 99;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           2
     */
    protected int $columns = self::ROWS_DEFAULT;
    /**
     * This field must be:
     * 1 = true
     * 0 = false
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected int $percentage = 1;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $start_range = 0;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $end_range = 100;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $step_range = xlvoNumberRangeSubFormGUI::STEP_RANGE_DEFAULT_VALUE;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected int $alt_result_display_mode;
    /**
     * @var bool
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected $randomise_option_sequence = 0;
    /** @var xlvoOption[] */
    protected array $voting_options = [];
    protected ?xlvoOption $first_voting_option = null;
    /**
     * @db_has_field true
     * @db_fieldtype integer
     * @db_length    1
     */
    protected int $answer_field = xlvoFreeInputSubFormGUI::ANSWER_FIELD_SINGLE_LINE;

    public static function returnDbTableName(): string
    {
        return self::TABLE_NAME;
    }

    public static function findOrGetInstance($primary_key, array $add_constructor_args = []): xlvoVoting
    {
        return parent::findOrGetInstance($primary_key, $add_constructor_args);
    }

    public function getConnectorContainerName(): string
    {
        return self::TABLE_NAME;
    }

    public function getComputedColums()
    {
        return (12 / (in_array($this->getColumns(), array(
                1,
                2,
                3,
                4,
            )) ? $this->getColumns() : self::ROWS_DEFAULT));
    }

    public function getColumns(): int
    {
        return $this->columns;
    }

    public function setColumns(int $columns): void
    {
        $this->columns = $columns;
    }

    /**
     * @throws Exception
     * @throws arException
     */
    public function fullClone(bool $change_name = true, bool $clone_options = true, int $new_obj_id = null): self
    {
        /**
         * @var xlvoVoting $newObj
         * @var xlvoOption $votingOptionNew
         */
        $newObj = $this->copy();
        if ($new_obj_id) {
            $newObj->setObjId($new_obj_id);
        }
        if ($change_name) {
            $count = 1;
            while (self::where(['title' => $newObj->getTitle() . ' (' . $count . ')'])->where(
                ['obj_id' => $newObj->getObjId()]
            )
                       ->count()) {
                $count++;
            }

            $newObj->setTitle($newObj->getTitle() . ' (' . $count . ')');
        }
        $newObj->store();
        if ($clone_options) {
            foreach ($newObj->getVotingOptions() as $votingOption) {
                $votingOptionNew = $votingOption->copy();
                $votingOptionNew->setVotingId($newObj->getId());
                $votingOptionNew->store();
            }
            $newObj->regenerateOptionSorting();
        }

        return $newObj;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function setObjId(int $obj_id): void
    {
        $this->obj_id = $obj_id;
    }

    /**
     * @return xlvoOption[]
     */
    public function getVotingOptions(): array
    {
        return $this->voting_options;
    }

    /**
     * @param xlvoOption[] $voting_options
     */
    public function setVotingOptions(array $voting_options): void
    {
        $this->voting_options = $voting_options;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function regenerateOptionSorting(): void
    {
        $i = 1;
        foreach ($this->getVotingOptions() as $votingOption) {
            $votingOption->setPosition($i);
            $votingOption->store();
            $i++;
        }
    }

    public function create(): void
    {
        $res = self::dic()->database()->query(
            'SELECT MAX(position) AS max FROM ' . self::TABLE_NAME . ' WHERE obj_id = ' . self::dic()->database()
                                                                                              ->quote(
                                                                                                  $this->getObjId(),
                                                                                                  'integer'
                                                                                              )
        );
        $data = self::dic()->database()->fetchObject($res);
        $this->setPosition($data->max + 1);
        parent::create();
    }

    public function isFirst(): bool
    {
        /**
         * @var xlvoVoting $first
         */
        $first = $this->getFirstLastList('ASC')->first();
        if (!$first instanceof self) {
            $first = new self();
        }

        return $first->getId() === $this->getId();
    }

    protected function getFirstLastList($order): ActiveRecordList
    {
        return self::where(array('obj_id' => $this->getObjId()))->orderBy('position', $order)
                   ->where(array('voting_type' => xlvoQuestionTypes::getActiveTypes()));
    }

    public function isLast(): bool
    {
        /**
         * @var xlvoVoting $first
         */
        $first = $this->getFirstLastList('DESC')->first();

        if (!$first instanceof self) {
            $first = new self();
        }

        return $first->getId() === $this->getId();
    }

    public function isMultiSelection(): bool
    {
        return $this->multi_selection;
    }

    public function setMultiSelection(bool $multi_selection): void
    {
        $this->multi_selection = $multi_selection;
    }

    public function isColors(): bool
    {
        return $this->colors;
    }

    public function setColors(bool $colors): void
    {
        $this->colors = $colors;
    }

    public function isMultiFreeInput(): bool
    {
        return $this->multi_free_input;
    }

    public function setMultiFreeInput(bool $multi_free_input): void
    {
        $this->multi_free_input = $multi_free_input;
    }

    /**
     * @throws arException
     */
    public function afterObjectLoad(): void
    {
        /**
         * @var xlvoOption[] $xlvoOptions
         * @var xlvoOption   $first_voting_option
         */
        $xlvoOptions = xlvoOption::where(array('voting_id' => $this->id))->orderBy('position')->get();
        $this->setVotingOptions($xlvoOptions);
        $first_voting_option = xlvoOption::where(array('voting_id' => $this->id))->orderBy('position')->first();
        $this->setFirstVotingOption($first_voting_option);
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setQuestion(string $question): void
    {
        $this->question = $question;
    }

    public function getQuestionForPresentation(): string
    {
        return ilLegacyFormElementsUtil::prepareTextareaOutput($this->getQuestionForEditor(), true);
    }

    public function getQuestionForEditor(): string
    {
        try {
            $prepared = ilRTE::_replaceMediaObjectImageSrc($this->question, 1);
        } catch (ilObjectTypeMismatchException $e) {
            return $this->question;
        }

        return $prepared;
    }

    public function getVotingStatus(): int
    {
        return $this->voting_status;
    }

    public function setVotingStatus(int $voting_status): void
    {
        $this->voting_status = $voting_status;
    }

    public function getFirstVotingOption(): ?xlvoOption
    {
        return $this->first_voting_option;
    }

    public function setFirstVotingOption(xlvoOption $first_voting_option): void
    {
        $this->first_voting_option = $first_voting_option;
    }

    public function getPercentage(): int
    {
        return $this->percentage;
    }

    public function setPercentage(int $percentage): xlvoVoting
    {
        $this->percentage = $percentage;

        return $this;
    }

    public function getStartRange(): int
    {
        return $this->start_range;
    }

    public function setStartRange(int $start_range): self
    {
        $this->start_range = $start_range;

        return $this;
    }

    public function getEndRange(): int
    {
        return $this->end_range;
    }

    public function setEndRange(int $end_range): self
    {
        $this->end_range = $end_range;

        return $this;
    }

    public function getStepRange(): int
    {
        return $this->step_range;
    }

    public function setStepRange(int $step_range): self
    {
        $this->step_range = $step_range;

        return $this;
    }

    public function getAltResultDisplayMode(): int
    {
        return $this->alt_result_display_mode;
    }

    public function setAltResultDisplayMode(int $alt_result_display_mode): self
    {
        $this->alt_result_display_mode = $alt_result_display_mode;

        return $this;
    }

    public function getRandomiseOptionSequence(): bool
    {
        return boolval($this->randomise_option_sequence);
    }

    public function setRandomiseOptionSequence(bool $randomise_option_sequence): xlvoVoting
    {
        $this->randomise_option_sequence = boolval($randomise_option_sequence);

        return $this;
    }

    public function _toJson(): stdClass
    {
        $class = new stdClass();
        $class->Id = $this->getId();
        $class->Title = $this->getTitle();
        $class->QuestionType = xlvoQuestionTypes::getClassName($this->getVotingType());
        $class->QuestionTypeId = (int) $this->getVotingType();
        $class->Question = $this->getRawQuestion();
        $class->Position = $this->getPosition();
        foreach ($this->getVotingOptions() as $xlvoOption) {
            $class->Options[] = $xlvoOption->_toJson();
        }

        return $class;
    }

    public function getVotingType(): string
    {
        return $this->voting_type;
    }

    public function setVotingType(string $voting_type): void
    {
        $this->voting_type = $voting_type;
    }

    public function getRawQuestion(): string
    {
        return trim(preg_replace('/\s+/', ' ', strip_tags($this->question)));
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getAnswerField(): int
    {
        return $this->answer_field;
    }

    public function setAnswerField(int $answer_field): void
    {
        $this->answer_field = $answer_field;
    }
}
