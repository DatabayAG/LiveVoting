<?php

declare(strict_types=1);

namespace LiveVoting\Cache\Version\v52;

use Exception;
use ilApc;
use ilGlobalCache;
use ilGlobalCacheService;
use ilLiveVotingPlugin;
use ilMemcache;
use ilStaticCache;
use ilXcache;
use LiveVoting\Cache\Initialisable;
use LiveVoting\Cache\xlvoCacheService;
use LiveVoting\Conf\xlvoConf;
use RuntimeException;
use srag\DIC\LiveVoting\DICTrait;

/**
 * Class xoctCache
 *
 * @package LiveVoting\Cache\Version\v52
 * @author  nschaefli
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class xlvoCache extends ilGlobalCache implements xlvoCacheService, Initialisable
{
    use DICTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    protected static bool $override_active = false;
    protected static array $active_components = [
        ilLiveVotingPlugin::PLUGIN_ID,
    ];

    public static function getInstance(?string $component): xlvoCache
    {
        $service_type = self::getSettings()->getService();
        $xlvoCache = new self($service_type);

        //must be disabled because the xlvoConf loads the data via xlvoCache which is not fully initialised at this point.
        $xlvoCache->setActive(false);
        self::setOverrideActive(false);

        return $xlvoCache;
    }

    public function init(): void
    {
        $this->initCachingService();
        $this->setActive(true);
        self::setOverrideActive(true);
    }

    protected function initCachingService(): void
    {
        /**
         * @var ilGlobalCacheService $ilGlobalCacheService
         */
        if (!$this->getComponent()) {
            $this->setComponent(ilLiveVotingPlugin::PLUGIN_NAME);
        }

        $ilGlobalCacheService = null;

        if ($this->isLiveVotingCacheEnabled()) {
            $serviceName = self::lookupServiceClassName($this->getServiceType());
            $ilGlobalCacheService = new $serviceName(self::$unique_service_id, $this->getComponent());
            $ilGlobalCacheService->setServiceType($this->getServiceType());
        } else {
            $serviceName = self::lookupServiceClassName(self::TYPE_STATIC);
            $ilGlobalCacheService = new $serviceName(self::$unique_service_id, $this->getComponent());
            $ilGlobalCacheService->setServiceType(self::TYPE_STATIC);
        }

        $this->global_cache = $ilGlobalCacheService;
        $this->setActive(in_array($this->getComponent(), self::getActiveComponents(), true));
    }

    private function isLiveVotingCacheEnabled(): ?bool
    {
        try {
            return (int) xlvoConf::getConfig(xlvoConf::F_USE_GLOBAL_CACHE) === 1;
        } catch (Exception $exceptione) { //catch exception while dbupdate is running. (xlvoConf is not ready at that time).
            return false;
        }
    }

    public static function lookupServiceClassName(int $service_type): string
    {
        switch ($service_type) {
            case self::TYPE_APC:
                return ilApc::class;
                break;
            case self::TYPE_MEMCACHED:
                return ilMemcache::class;
                break;
            case self::TYPE_XCACHE:
                return ilXcache::class;
                break;
            case self::TYPE_STATIC:
                return ilStaticCache::class;
                break;
            default:
                return ilStaticCache::class;
                break;
        }
    }

    public static function getActiveComponents(): array
    {
        return self::$active_components;
    }

    /**
     * @throws RuntimeException
     */
    public function flush(bool $complete = false): bool
    {
        if (!$this->global_cache instanceof ilGlobalCacheService || !$this->isActive()) {
            return false;
        }

        return parent::flush($complete);
    }

    public function isActive(): bool
    {
        return self::isOverrideActive();
    }

    public static function isOverrideActive(): bool
    {
        return self::$override_active;
    }

    public static function setOverrideActive(bool $override_active): void
    {
        self::$override_active = $override_active;
    }

    /**
     * @throws RuntimeException
     */
    public function delete(string $key): bool
    {
        if (!$this->global_cache instanceof ilGlobalCacheService || !$this->isActive()) {
            return false;
        }

        return parent::delete($key);
    }

    /**
     * @param mixed $value Serializable object or string.
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        //		$ttl = $ttl ? $ttl : 480;
        if (!$this->global_cache instanceof ilGlobalCacheService || !$this->isActive()) {
            return false;
        }

        return $this->global_cache->set($key, $this->global_cache->serialize($value), $ttl);
    }

    /**
     * @param $key
     *
     * @return bool|mixed|null
     */
    public function get($key)
    {
        if (!$this->global_cache instanceof ilGlobalCacheService || !$this->isActive()) {
            return false;
        }
        $unserialized_return = $this->global_cache->unserialize($this->global_cache->get($key));

        if ($unserialized_return) {
            return $unserialized_return;
        }

        return null;
    }
}
