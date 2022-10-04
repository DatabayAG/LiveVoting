<?php

declare(strict_types=1);

namespace LiveVoting\Cache;

use ActiveRecord;
use arConnector;
use arConnectorDB;
use ilLiveVotingPlugin;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

/**
 * @author  nschaefli
 */
abstract class CachingActiveRecord extends ActiveRecord
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;

    public function __construct(int $primary_key = 0, arConnector $connector = null)
    {
        $arConnector = $connector;
        if (is_null($arConnector)) {
            $arConnector = new arConnectorCache(new arConnectorDB());
        }

        parent::__construct($primary_key);
    }
}
