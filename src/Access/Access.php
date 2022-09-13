<?php

declare(strict_types=1);

namespace LiveVoting\Access;

use ilLiveVotingPlugin;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

/**
 * Class Access
 *
 * @package LiveVoting\Access
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
final class Access
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    /**
     * @var self
     */
    protected static $instance = null;

    /**
     * Access constructor
     */
    private function __construct()
    {
    }

    /**
     * @return self
     */
    public static function getInstance()/*: self*/
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
