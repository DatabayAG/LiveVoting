<?php

declare(strict_types=1);

namespace LiveVoting\QuestionTypes\FreeInput;

use LiveVoting\Cache\CachingActiveRecord;

/**
 * Class xlvoFreeInputCategory
 *
 * @package LiveVoting\QuestionTypes\FreeInput
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xlvoFreeInputCategory extends CachingActiveRecord
{
    public const TABLE_NAME = 'rep_robj_xlvo_cat';
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
     * @var string
     *
     * @db_has_field true
     * @db_fieldtype text
     * @db_length    256
     */
    protected $title;
    /**
     * @var int
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected $voting_id;
    /**
     * @var int
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected $round_id;

    /**
     * @return string
     * @deprecated
     */
    public static function returnDbTableName()
    {
        return self::TABLE_NAME;
    }

    /**
     * @return string
     */
    public function getConnectorContainerName()
    {
        return self::TABLE_NAME;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getVotingId()
    {
        return $this->voting_id;
    }

    /**
     * @param int $voting_id
     */
    public function setVotingId($voting_id)
    {
        $this->voting_id = $voting_id;
    }

    /**
     * @return int
     */
    public function getRoundId()
    {
        return $this->round_id;
    }

    /**
     * @param int $round_id
     */
    public function setRoundId($round_id)
    {
        $this->round_id = $round_id;
    }
}
