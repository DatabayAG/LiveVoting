<?php

declare(strict_types=1);

namespace LiveVoting\User;

use ilLiveVotingPlugin;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

/**
 * Class xlvoUser
 *
 * @package LiveVoting\User
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class xlvoUser
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    public const TYPE_ILIAS = 1;
    public const TYPE_PIN = 2;
    /**
     * @var xlvoUser
     */
    protected static $instance;
    /**
     * @var int
     */
    protected $type = self::TYPE_ILIAS;
    /**
     * @var string
     */
    protected $identifier = '';

    /**
     * xlvoUser constructor.
     */
    protected function __construct()
    {
    }

    /**
     * @return xlvoUser
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return bool
     */
    public function isILIASUser()
    {
        return ($this->getType() == self::TYPE_ILIAS);
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**$
     * @return bool
     */
    public function isPINUser()
    {
        return ($this->getType() == self::TYPE_PIN);
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param $identifier
     *
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }
}
