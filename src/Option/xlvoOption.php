<?php

declare(strict_types=1);

namespace LiveVoting\Option;

use ilRTE;
use ilUtil;
use LiveVoting\Cache\CachingActiveRecord;
use stdClass;
use ilLegacyFormElementsUtil;

class xlvoOption extends CachingActiveRecord
{
    public const STAT_INACTIVE = 0;
    public const STAT_ACTIVE = 1;
    public const TABLE_NAME = 'rep_robj_xlvo_option_n';
    /**
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           256
     */
    protected string $text;
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
    protected int $voting_id;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $type;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $status = self::STAT_ACTIVE;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $position;
    /**
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected ?string $correct_position = null;

    /**
     * @deprecated
     */
    public static function returnDbTableName(): string
    {
        return self::TABLE_NAME;
    }

    public function getConnectorContainerName(): string
    {
        return self::TABLE_NAME;
    }

    public function getTextForPresentation(): string
    {
        return ilLegacyFormElementsUtil::prepareTextareaOutput($this->getTextForEditor(), true);
    }

    public function getTextForEditor(): string
    {
        return ilRTE::_replaceMediaObjectImageSrc($this->text, 1);
    }

    public function getCipher(): string
    {
        return chr($this->getPosition() + 64);
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getVotingId(): int
    {
        return $this->voting_id;
    }

    public function setVotingId(int $voting_id): void
    {
        $this->voting_id = $voting_id;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getCorrectPosition(): ?string
    {
        return $this->correct_position;
    }

    public function setCorrectPosition(string $correct_position): void
    {
        $this->correct_position = $correct_position;
    }

    public function _toJson(): stdClass
    {
        $class = new stdClass();
        $class->Id = $this->getId();
        $class->Text = $this->getText();
        $class->Position = $this->getPosition();

        return $class;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }
}
