<?php

declare(strict_types=1);

namespace LiveVoting\Cache;

use ilLiveVotingPlugin;
use LiveVoting\Cache\Version\v52\xlvoCache;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

/**
 * @author  nschaefli
 */
class xlvoCacheFactory
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    private static $cache_instance;

    public static function getInstance(): ?xlvoCacheService
    {
        if (!isset(self::$cache_instance)) {
            self::$cache_instance = xlvoCache::getInstance('');

            /*
             * caching adapter of the xlvoConf will call getInstance again,
             * due to that we need to call the init logic after we created the
             * cache in an deactivated state.
             *
             * The xlvoConf call gets the deactivated cache and query the value
             * out of the database. afterwards the cache is turned on with this init() call.
             *
             * This must be considered as workaround and should be probably fixed in the next major release.
             */
            self::$cache_instance->init();
        }

        return self::$cache_instance;
    }
}
