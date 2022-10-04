<?php

declare(strict_types=1);

namespace LiveVoting\Voter;

use DateTime;
use LiveVoting\Cache\CachingActiveRecord;
use LiveVoting\Conf\xlvoConf;
use LiveVoting\User\xlvoUser;
use DateTimeInterface;

class xlvoVoter extends CachingActiveRecord
{
    public const DEFAULT_CLIENT_UPDATE_DELAY = 1;
    public const TABLE_NAME = 'xlvo_voter';
    /**
     * @con_is_primary true
     * @con_is_unique  true
     * @con_sequence   true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected int $id;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected int $player_id = 0;
    /**
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     128
     */
    protected string $user_identifier;
    /**
     * @con_has_field  true
     * @con_fieldtype  timestamp
     */
    protected DateTime $last_access;

    /**
     * @deprecated
     */
    public static function returnDbTableName(): string
    {
        return self::TABLE_NAME;
    }

    public static function register(int $player_id): void
    {
        $obj = self::where([
            'user_identifier' => xlvoUser::getInstance()->getIdentifier(),
            'player_id' => $player_id
        ])->first();

        if (!$obj instanceof self) {
            $obj = new self();
            $obj->setUserIdentifier(xlvoUser::getInstance()->getIdentifier());
            $obj->setPlayerId($player_id);
        }
        $obj->setLastAccess(new DateTime());
        $obj->store();
    }

    public static function countVoters(int $player_id): int
    {
        $delay = xlvoConf::getConfig(xlvoConf::F_REQUEST_FREQUENCY);

        //check if we get some valid settings otherwise fall back to default value.
        if (is_numeric($delay)) {
            $delay = ((float) $delay);
        } else {
            $delay = self::DEFAULT_CLIENT_UPDATE_DELAY;
        }

        return self::where(['player_id' => $player_id])->where([
            'last_access' => date(DATE_ATOM, time() - ($delay + $delay * 0.5))
        ], '>')->count();
    }

    public function getConnectorContainerName(): string
    {
        return self::TABLE_NAME;
    }

    public function sleep($field_name): ?string
    {
        if ($field_name === 'last_access') {
            if (!$this->last_access instanceof DateTime) {
                $this->last_access = new DateTime();
            }

            return $this->last_access->format(DateTimeInterface::ATOM);
        }

        return null;
    }

    public function wakeUp($field_name, $field_value): ?DateTime
    {
        if ($field_name === 'last_access') {
            return new DateTime($field_value);
        }

        return null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getPlayerId(): int
    {
        return $this->player_id;
    }

    public function setPlayerId(int $player_id): void
    {
        $this->player_id = $player_id;
    }

    public function getUserIdentifier(): string
    {
        return $this->user_identifier;
    }

    public function setUserIdentifier(string $user_identifier): void
    {
        $this->user_identifier = $user_identifier;
    }

    public function getLastAccess(): DateTime
    {
        return $this->last_access;
    }

    public function setLastAccess(DateTime $last_access): void
    {
        $this->last_access = $last_access;
    }
}
