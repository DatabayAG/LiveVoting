<?php

declare(strict_types=1);

namespace LiveVoting\Cache;

use ActiveRecord;
use ActiveRecordList;
use arConnector;
use arException;
use ilLiveVotingPlugin;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;
use stdClass;

/**
 * Class arConnectorCache
 *
 * @package LiveVoting\Cache
 * @author  nschaefli
 */
class arConnectorCache extends arConnector
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    public const CACHE_TTL_SECONDS = 1800;
    private $arConnectorDB;
    private $cache;

    public function __construct(arConnector $arConnectorDB)
    {
        $this->arConnectorDB = $arConnectorDB;
        $this->cache = xlvoCacheFactory::getInstance();
    }

    /**
     * @return mixed
     */
    public function nextID(ActiveRecord $ar)
    {
        return $this->arConnectorDB->nextID($ar);
    }

    public function checkConnection(ActiveRecord $ar): bool
    {
        return $this->arConnectorDB->checkConnection($ar);
    }

    public function installDatabase(ActiveRecord $ar, $fields): bool
    {
        return $this->arConnectorDB->installDatabase($ar, $fields);
    }

    public function updateDatabase(ActiveRecord $ar): bool
    {
        return $this->arConnectorDB->updateDatabase($ar);
    }

    public function resetDatabase(ActiveRecord $ar): bool
    {
        return $this->arConnectorDB->resetDatabase($ar);
    }

    public function truncateDatabase(ActiveRecord $ar): bool
    {
        return $this->arConnectorDB->truncateDatabase($ar);
    }

    public function checkTableExists(ActiveRecord $ar): bool
    {
        return $this->arConnectorDB->checkTableExists($ar);
    }

    public function checkFieldExists(ActiveRecord $ar, string $field_name): bool
    {
        return $this->arConnectorDB->checkFieldExists($ar, $field_name);
    }

    /**
     * @throws arException
     */
    public function removeField(ActiveRecord $ar, string $field_name): bool
    {
        return $this->arConnectorDB->removeField($ar, $field_name);
    }

    /**
     * @throws arException
     */
    public function renameField(ActiveRecord $ar, string $old_name, string $new_name): bool
    {
        return $this->arConnectorDB->renameField($ar, $old_name, $new_name);
    }

    public function create(ActiveRecord $ar): void
    {
        $this->arConnectorDB->create($ar);
        $this->storeActiveRecordInCache($ar);
    }

    private function storeActiveRecordInCache(ActiveRecord $ar): void
    {
        if ($this->cache->isActive()) {
            $key = $ar->getConnectorContainerName() . "_" . $ar->getPrimaryFieldValue();
            $value = $ar->__asStdClass();

            $this->cache->set($key, $value, self::CACHE_TTL_SECONDS);
        }
    }

    public function read(ActiveRecord $ar): array
    {
        if ($this->cache->isActive()) {
            $key = $ar->getConnectorContainerName() . "_" . $ar->getPrimaryFieldValue();
            $cached_value = $this->cache->get($key);
            if (is_array($cached_value)) {
                return $cached_value;
            }

            if ($cached_value instanceof stdClass) {
                return [$cached_value];
            }
        }

        $results = $this->arConnectorDB->read($ar);

        if ($this->cache->isActive()) {
            $key = $ar->getConnectorContainerName() . "_" . $ar->getPrimaryFieldValue();

            $this->cache->set($key, $results, self::CACHE_TTL_SECONDS);
        }

        return $results;
    }

    public function update(ActiveRecord $ar): void
    {
        $this->arConnectorDB->update($ar);
        $this->storeActiveRecordInCache($ar);
    }

    public function delete(ActiveRecord $ar): void
    {
        $this->arConnectorDB->delete($ar);

        if ($this->cache->isActive()) {
            $key = $ar->getConnectorContainerName() . "_" . $ar->getPrimaryFieldValue();
            $this->cache->delete($key);
        }
    }

    public function readSet(ActiveRecordList $arl): array
    {
        return $this->arConnectorDB->readSet($arl);
    }

    public function affectedRows(ActiveRecordList $arl): int
    {
        return $this->arConnectorDB->affectedRows($arl);
    }

    public function quote($value, string $type): string
    {
        return $this->arConnectorDB->quote($value, $type);
    }

    public function updateIndices(ActiveRecord $ar): void
    {
        $this->arConnectorDB->updateIndices($ar);
    }
}
