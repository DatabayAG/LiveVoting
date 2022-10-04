<?php

declare(strict_types=1);

namespace LiveVoting\Conf;

use ilSetting;
use LiveVoting\Cache\CachingActiveRecord;
use arConnector;

/**
 * @author     Fabian Schmid <fs@studer-raimann.ch>
 * @deprecated Use srag\ActiveRecordConfig\LiveVoting
 */
class xlvoConf extends CachingActiveRecord
{
    /**
     * @deprecated
     */
    public const CONFIG_VERSION = 2;
    /**
     * @deprecated
     */
    public const F_CONFIG_VERSION = 'config_version';
    /**
     * @deprecated
     */
    public const F_ALLOW_FREEZE = 'allow_freeze';
    /**
     * @deprecated
     */
    public const F_ALLOW_FULLSCREEN = 'allow_fullscreen';
    /**
     * @deprecated
     */
    public const F_ALLOW_SHORTLINK_VOTE = 'allow_shortlink';
    /**
     * @deprecated
     */
    public const F_ALLOW_SHORTLINK_VOTE_LINK = 'allow_shortlink_link';
    /**
     * @deprecated
     */
    public const F_BASE_URL_VOTE = 'base_url';
    /**
     * @deprecated
     */
    public const F_ALLOW_GLOBAL_ANONYMOUS = 'global_anonymous';
    /**
     * @deprecated
     */
    public const F_REGENERATE_TOKEN = 'regenerate_token';
    /**
     * @deprecated
     */
    public const F_USE_QR = 'use_qr';
    /**
     * @deprecated
     */
    public const REWRITE_RULE_VOTE = "RewriteRule ^/?vote(/\\w*)? /Customizing/global/plugins/Services/Repository/RepositoryObject/LiveVoting/pin.php?xlvo_pin=$1 [L]";
    /**
     * @deprecated
     */
    public const API_URL = './Customizing/global/plugins/Services/Repository/RepositoryObject/LiveVoting/ilias.php';
    /**
     * @deprecated
     */
    public const RESULT_API_URL = './Customizing/global/plugins/Services/Repository/RepositoryObject/LiveVoting/api.php';
    /**
     * @deprecated
     */
    public const F_REQUEST_FREQUENCY = 'request_frequency';
    /**
     * @deprecated
     */
    public const F_RESULT_API = 'result_api';
    /**
     * @deprecated
     */
    public const F_USE_SERIF_FONT_FOR_PINS = 'use_serif_font_for_pins';
    /**
     * @deprecated
     */
    public const F_API_TYPE = 'api_type';
    /**
     * @deprecated
     */
    public const F_API_TOKEN = 'api_token';
    /**
     * @deprecated
     */
    public const F_USE_GLOBAL_CACHE = 'use_global_cache';
    /**
     * @deprecated
     */
    public const F_ACTIVATE_POWERPOINT_EXPORT = 'ppt_export';
    /**
     * @deprecated
     */
    public const F_ALLOW_SHORTLINK_PRESENTER = 'allow_shortlink_presenter';
    /**
     * @deprecated
     */
    public const F_ALLOW_SHORTLINK_PRESENTER_LINK = 'allow_shortlink_link_presenter';
    /**
     * @deprecated
     */
    public const REWRITE_RULE_PRESENTER = "RewriteRule ^/?presenter(/\\w*)(/\\w*)(/\\w*)?(/\\w*)? /Customizing/global/plugins/Services/Repository/RepositoryObject/LiveVoting/presenter.php?xlvo_pin=$1&xlvo_puk=$2&xlvo_voting=$3&xlvo_ppt=$4 [L]";
    /**
     * Min client update frequency in seconds.
     * This value should never be set bellow 1 second.
     *
     * @deprecated
     */
    public const MIN_CLIENT_UPDATE_FREQUENCY = 1;
    /**
     * Max client update frequency in seconds.
     *
     * @deprecated
     */
    public const MAX_CLIENT_UPDATE_FREQUENCY = 60;
    /**
     * @deprecated
     */
    public const TABLE_NAME = 'xlvo_config';
    /**
     * @deprecated
     */
    protected static array $cache = [];
    /**
     * @deprecated
     */
    protected static array $cache_loaded = [];
    /**
     * @deprecated
     */
    protected bool $ar_safe_read = false;
    /**
     * @db_has_field        true
     * @db_is_unique        true
     * @db_is_primary       true
     * @db_is_notnull       true
     * @db_fieldtype        text
     * @db_length           250
     *
     * @deprecated
     */
    protected string $name;
    /**
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           4000
     *
     * @deprecated
     */
    protected string $value;

    /**
     * @deprecated
     */
    public static function returnDbTableName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * @deprecated
     */
    public static function isLatexEnabled(): bool
    {
        $mathJaxSetting = new ilSetting("MathJax");

        return (bool) $mathJaxSetting->get("enable");
    }

    /**
     * @deprecated
     */
    public static function getApiToken(): string
    {
        $token = self::getConfig(self::F_API_TOKEN);
        if (!$token) {
            $token = md5((string) time()); // TODO: Use other not depcreated, safer hash algo (Like `hash("sha256", $hash)`)
            self::set(self::F_API_TOKEN, $token);
        }

        return $token;
    }

    /**
     * @return mixed
     * @deprecated
     */
    public static function getConfig($name)
    {
        if (!self::$cache_loaded[$name]) {
            // TODO
            $obj = new self($name);
            self::$cache[$name] = json_decode($obj->getValue(), false, 512, JSON_THROW_ON_ERROR);
            self::$cache_loaded[$name] = true;
        }

        return self::$cache[$name];
    }

    /**
     * @deprecated
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @deprecated
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * @deprecated
     */
    public static function set($name, $value): void
    {
        $obj = new self($name);
        $obj->setValue(json_encode($value, JSON_THROW_ON_ERROR));

        $obj->store();
    }

    /**
     * @deprecated
     */
    public static function getFullApiURL(): string
    {
        return self::getBaseVoteURL() . ltrim(self::API_URL, "./");
    }

    /**
     * @deprecated
     */
    public static function getBaseVoteURL(): string
    {
        if (self::getConfig(self::F_ALLOW_SHORTLINK_VOTE)) {
            $url = self::getConfig(self::F_BASE_URL_VOTE);
            $url = rtrim($url, "/") . "/";
        } else {
            $str = strstr(ILIAS_HTTP_PATH, 'Customizing', true);
            $url = rtrim($str, "/") . "/";
        }

        return $url;
    }

    /**
     * @deprecated
     */
    public static function isConfigUpToDate(): bool
    {
        return self::getConfig(self::F_CONFIG_VERSION) === self::CONFIG_VERSION;
    }

    /**
     * @deprecated
     */
    public static function load(): void
    {
        parent::get();
    }

    /**
     * @deprecated
     */
    public static function remove(string $name): void
    {
        /**
         * @var xlvoConf $obj
         */
        $obj = self::find($name);
        $obj?->delete();
    }

    /**
     * @deprecated
     */
    public function getConnectorContainerName(): string
    {
        return self::TABLE_NAME;
    }

    /**
     * @deprecated
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @deprecated
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
