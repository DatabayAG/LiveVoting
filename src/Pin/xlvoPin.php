<?php

declare(strict_types=1);

namespace LiveVoting\Pin;

use ilLiveVotingPlugin;
use ilObjLiveVotingAccess;
use LiveVoting\Cache\xlvoCacheFactory;
use LiveVoting\Cache\xlvoCacheService;
use LiveVoting\Conf\xlvoConf;
use LiveVoting\Context\Param\ParamManager;
use LiveVoting\Exceptions\xlvoVoterException;
use LiveVoting\User\xlvoUser;
use LiveVoting\Utils\LiveVotingTrait;
use LiveVoting\Voting\xlvoVotingConfig;
use srag\DIC\LiveVoting\DICTrait;
use stdClass;

class xlvoPin
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    public const CACHE_TTL_SECONDS = 1800;
    private ?xlvoCacheService $cache;
    protected string $pin = '';
    protected bool $use_lowercase = false;
    protected bool $use_uppercase = true;
    protected bool $use_numbers = true;
    protected int $pin_length = 4;

    public function __construct(string $pin = '')
    {
        if (!$pin) {
            $this->generatePIN();
        } else {
            $this->setPin($pin);
        }

        $this->cache = xlvoCacheFactory::getInstance();
    }

    protected function generatePIN(): void
    {
        $array = [];

        // numbers
        if ($this->isUseNumbers()) {
            for ($i = 48; $i < 58; $i++) {
                $array[] = chr($i);
            }
        }

        // lower case
        if ($this->isUseLowercase()) {
            for ($i = 97; $i <= 122; $i++) {
                $array[] = chr($i);
            }
        }

        // upper case
        if ($this->isUseUppercase()) {
            for ($i = 65; $i <= 90; $i++) {
                $array[] = chr($i);
            }
        }

        $pin = '';
        $pin_found = false;

        while (!$pin_found) {
            for ($i = 1; $i <= $this->getPinLength(); $i++) {
                $rnd = random_int(0, count($array) - 1);
                $pin .= $array[$rnd];
            }
            if (xlvoVotingConfig::where(['pin' => $pin])->count() <= 0) {
                $pin_found = true;
            }
        }

        $this->setPin($pin);
    }

    public function isUseNumbers(): bool
    {
        return $this->use_numbers;
    }

    public function setUseNumbers(bool $use_numbers): void
    {
        $this->use_numbers = $use_numbers;
    }

    public function isUseLowercase(): bool
    {
        return $this->use_lowercase;
    }

    public function setUseLowercase(bool $use_lowercase): void
    {
        $this->use_lowercase = $use_lowercase;
    }

    public function isUseUppercase(): bool
    {
        return $this->use_uppercase;
    }

    public function setUseUppercase(bool $use_uppercase): void
    {
        $this->use_uppercase = $use_uppercase;
    }

    public function getPinLength(): int
    {
        return $this->pin_length;
    }

    public function setPinLength(int $pin_length): void
    {
        $this->pin_length = $pin_length;
    }

    public static function formatPin(string $pin, bool $force_not_format = false): string
    {
        if (!$force_not_format && xlvoConf::getConfig(xlvoConf::F_USE_SERIF_FONT_FOR_PINS)) {
            $pin = '<span class="serif_font">' . $pin . '</span>';
        }

        return $pin;
    }

    public static function lookupPin(int $obj_id): int
    {
        /**
         * @var xlvoVotingConfig $xlvoVotingConfig
         */
        $xlvoVotingConfig = xlvoVotingConfig::findOrGetInstance($obj_id);

        return $xlvoVotingConfig->getPin();
    }

    public static function checkPinAndGetObjId(string $pin, $safe_mode = true): int
    {
        $cache = xlvoCacheFactory::getInstance();

        if ($cache && $cache->isActive()) {
            return self::checkPinAndGetObjIdWithCache($pin, $safe_mode);
        }

        return self::checkPinAndGetObjIdWithoutCache($pin, $safe_mode);
    }

    /**
     * @throws xlvoVoterException
     */
    private static function checkPinAndGetObjIdWithCache(string $pin, bool $safe_mode = true): int
    {
        global $DIC;

        //use cache to speed up pin fetch operation
        $key = xlvoVotingConfig::TABLE_NAME . '_pin_' . $pin;
        $cache = xlvoCacheFactory::getInstance();

        $config = $cache->get($key);
        $xlvoVotingConfig = null;

        if (!$config instanceof stdClass) {
            //save obj id for a later cache fetch
            //if we store the object a second time we would have some consistency problems because we don't know when the data are updated.
            $xlvoVotingConfig = xlvoVotingConfig::where(array('pin' => $pin))->first();
            $config = new stdClass();

            //if the object is not gone
            if ($xlvoVotingConfig instanceof xlvoVotingConfig) {
                $config->id = $xlvoVotingConfig->getPrimaryFieldValue();
                $cache->set($key, $config, self::CACHE_TTL_SECONDS);
            }
        }

        if (!($xlvoVotingConfig instanceof xlvoVotingConfig)) {
            $xlvoVotingConfig = xlvoVotingConfig::find($config->id); //relay on ar connector cache
        }

        $param_manager = ParamManager::getInstance();

        //check pin
        if ($xlvoVotingConfig instanceof xlvoVotingConfig) {
            if (!$xlvoVotingConfig->isObjOnline() && !ilObjLiveVotingAccess::hasWriteAccess(
                $param_manager->getRefId(),
                $DIC->user()->getId()
            )) {
                if ($safe_mode) {
                    throw new xlvoVoterException(
                        'The voting is currently offline.',
                        xlvoVoterException::VOTING_OFFLINE
                    );
                }
            }
            if (!$xlvoVotingConfig->isAnonymous() && xlvoUser::getInstance()->isPINUser()) {
                if ($safe_mode) {
                    throw new xlvoVoterException(
                        'The voting is not available for anonymous users.',
                        xlvoVoterException::VOTING_NOT_ANONYMOUS
                    );
                }
            }

            if (!$xlvoVotingConfig->isAvailableForUser() && xlvoUser::getInstance()->isPINUser()) {
                if ($safe_mode) {
                    throw new xlvoVoterException(
                        'The voting is currently unavailable.',
                        xlvoVoterException::VOTING_UNAVAILABLE
                    );
                }
            }

            return $xlvoVotingConfig->getObjId();
        }
        if ($safe_mode) {
            throw new xlvoVoterException('', xlvoVoterException::VOTING_PIN_NOT_FOUND);
        }

        return 0;
    }

    /**
     * @throws xlvoVoterException
     */
    private static function checkPinAndGetObjIdWithoutCache(string $pin, bool $safe_mode = true): int
    {
        $xlvoVotingConfig = xlvoVotingConfig::where(array('pin' => $pin))->first();

        //check pin
        if ($xlvoVotingConfig instanceof xlvoVotingConfig) {
            if (!$xlvoVotingConfig->isObjOnline()) {
                if ($safe_mode) {
                    throw new xlvoVoterException('', xlvoVoterException::VOTING_OFFLINE);
                }
            }
            if (!$xlvoVotingConfig->isAnonymous() && xlvoUser::getInstance()->isPINUser()) {
                if ($safe_mode) {
                    throw new xlvoVoterException('', xlvoVoterException::VOTING_NOT_ANONYMOUS);
                }
            }

            if (!$xlvoVotingConfig->isAvailableForUser() && xlvoUser::getInstance()->isPINUser()) {
                if ($safe_mode) {
                    throw new xlvoVoterException('', xlvoVoterException::VOTING_UNAVAILABLE);
                }
            }

            return $xlvoVotingConfig->getObjId();
        }
        if ($safe_mode) {
            throw new xlvoVoterException('', xlvoVoterException::VOTING_PIN_NOT_FOUND);
        }

        return 0;
    }

    public function getLastAccess(): ?string
    {
        if ($this->cache->isActive()) {
            return $this->getLastAccessWithCache();
        }

        return $this->getLastAccessWithoutCache();
    }

    private function getLastAccessWithCache(): ?string
    {
        $key = xlvoVotingConfig::TABLE_NAME . '_pin_' . $this->getPin();
        /**
         * @var stdClass $xlvoVotingConfig
         */
        $xlvoVotingConfig = $this->cache->get($key);

        if (!($xlvoVotingConfig instanceof stdClass)) {
            $xlvoVotingConfig = xlvoVotingConfig::where(array('pin' => $this->getPin()))->first();
            $config = new stdClass();

            //if the object is not gone
            if ($xlvoVotingConfig instanceof xlvoVotingConfig) {
                $config->id = $xlvoVotingConfig->getPrimaryFieldValue();
                $this->cache->set($key, $config, self::CACHE_TTL_SECONDS);

                return $xlvoVotingConfig->getLastAccess();
            }

            if (!($xlvoVotingConfig instanceof xlvoVotingConfig)) {
                return null;
            }
        }

        /**
         * @var xlvoVotingConfig $xlvoVotingConfigObject
         */
        $xlvoVotingConfigObject = xlvoVotingConfig::find($xlvoVotingConfig->id);

        return $xlvoVotingConfigObject->getLastAccess();
    }

    public function getPin(): string
    {
        return $this->pin;
    }

    public function setPin(string $pin): void
    {
        $this->pin = $pin;
    }

    private function getLastAccessWithoutCache(): ?string
    {
        $xlvoVotingConfig = xlvoVotingConfig::where(array('pin' => $this->getPin()))->first();

        if (!($xlvoVotingConfig instanceof xlvoVotingConfig)) {
            return null;
        }

        return $xlvoVotingConfig->getLastAccess();
    }
}
