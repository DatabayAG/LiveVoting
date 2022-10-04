<?php

declare(strict_types=1);

namespace LiveVoting\Round;

use LiveVoting\Cache\CachingActiveRecord;

class xlvoRound extends CachingActiveRecord
{
    public const TABLE_NAME = 'rep_robj_xlvo_round_n';
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
     * @db_length           8
     */
    protected int $obj_id;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $round_number;
    /**
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           256
     */
    protected string $title;

    /**
     * @deprecated
     */
    public static function returnDbTableName(): string
    {
        return self::TABLE_NAME;
    }

    public static function getLatestRound(int $obj_id): xlvoRound
    {
        return self::find(self::getLatestRoundId($obj_id));
    }

    public static function getLatestRoundId(int $obj_id): int
    {
        $q = "SELECT result.id FROM (SELECT id FROM " . self::TABLE_NAME . " WHERE " . self::TABLE_NAME
            . ".obj_id = %s) AS result ORDER BY result.id DESC LIMIT 1";
        //$q = "SELECT MAX(id) FROM " . self::TABLE_NAME . " WHERE obj_id = %s";
        $result = self::dic()->database()->queryF($q, array('integer'), array($obj_id));
        $data = self::dic()->database()->fetchObject($result);

        if (!isset($data->id)) {
            return self::createFirstRound($obj_id)->getId();
        }

        return $data->id;
    }

    public static function createFirstRound(int $obj_id): xlvoRound
    {
        $round = new xlvoRound();
        $round->setRoundNumber(1);
        $round->setObjId($obj_id);
        $round->store();

        return $round;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getConnectorContainerName(): string
    {
        return self::TABLE_NAME;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function setObjId(int $obj_id): void
    {
        $this->obj_id = $obj_id;
    }

    public function getRoundNumber(): int
    {
        return $this->round_number;
    }

    public function setRoundNumber(int $round_number): void
    {
        $this->round_number = $round_number;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
