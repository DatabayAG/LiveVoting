<?php

declare(strict_types=1);

namespace LiveVoting\Context\Param;

use ilLiveVotingPlugin;
use ilObject;
use ilUIPluginRouterGUI;
use LiveVoting\Pin\xlvoPin;
use LiveVoting\Utils\LiveVotingTrait;
use LiveVoting\Voting\xlvoVotingManager2;
use srag\DIC\LiveVoting\DICTrait;

/**
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
final class ParamManager
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    public const PARAM_BASE_CLASS_NAME = ilUIPluginRouterGUI::class;
    public const PARAM_REF_ID = 'ref_id';
    public const PARAM_PIN = 'xlvo_pin';
    public const PARAM_PUK = 'xlvo_puk';
    public const PARAM_VOTING = 'xlvo_voting';
    public const PARAM_PPT = 'xlvo_ppt';
    public const PPT_START = 'ppt_start';
    protected static self $instance;
    protected static xlvoVotingManager2 $instance_voting_manager2;
    protected int $ref_id;
    protected string $pin = '';
    protected string $puk = '';
    protected int $voting = 0;
    protected bool $ppt = false;

    public function __construct()
    {
        $this->loadBaseClassIfNecessary();

        $this->loadAndPersistAllParams();
    }

    private function loadBaseClassIfNecessary(): void
    {
        $baseClass = filter_input(INPUT_GET, "baseClass");

        if (empty($baseClass)) {
            self::dic()->ctrl()->initBaseClass(ilUIPluginRouterGUI::class);
        }
    }

    private function loadAndPersistAllParams(): void
    {
        $pin = trim(filter_input(INPUT_GET, self::PARAM_PIN), "/");
        if (!empty($pin)) {
            $this->setPin($pin);
        }

        $ref_id = trim(filter_input(INPUT_GET, self::PARAM_REF_ID), "/");
        if (!empty($ref_id)) {
            $this->setRefId((int) $ref_id);
        }

        $puk = trim(filter_input(INPUT_GET, self::PARAM_PUK), "/");
        if (!empty($puk)) {
            $this->setPuk($puk);
        }

        $voting = trim(filter_input(INPUT_GET, self::PARAM_VOTING), "/");
        if (!empty($voting)) {
            $this->setVoting((int) $voting);
        }

        $ppt = trim(filter_input(INPUT_GET, self::PARAM_PPT), "/");
        if (!empty($ppt)) {
            $this->setPpt((bool) $ppt);
        }
    }

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getRefId(): int
    {
        $ref_id = trim(filter_input(INPUT_GET, self::PARAM_REF_ID), "/");

        if (!empty($ref_id)) {
            $this->ref_id = (int) $ref_id;
        }

        if (empty($this->ref_id)) {
            $obj_id = xlvoPin::checkPinAndGetObjId($this->pin, false);

            $this->ref_id = current(ilObject::_getAllReferences($obj_id));
        }

        return $this->ref_id;
    }

    public function setRefId(int $ref_id): void
    {
        $this->ref_id = $ref_id;

        self::dic()->ctrl()->setParameterByClass(self::PARAM_BASE_CLASS_NAME, self::PARAM_REF_ID, $ref_id);
    }

    public function getPin(): string
    {
        return $this->pin;
    }

    /**
     * @param string $pin
     */
    public function setPin(string $pin): void
    {
        $this->pin = $pin;

        self::dic()->ctrl()->setParameterByClass(self::PARAM_BASE_CLASS_NAME, self::PARAM_PIN, $pin);
    }

    public function getPuk(): string
    {
        return $this->puk;
    }

    public function setPuk(string $puk): void
    {
        $this->puk = $puk;

        self::dic()->ctrl()->setParameterByClass(self::PARAM_BASE_CLASS_NAME, self::PARAM_PUK, $puk);
    }

    public function getVoting(): int
    {
        return $this->voting;
    }

    public function setVoting(int $voting): void
    {
        $this->voting = $voting;

        self::dic()->ctrl()->setParameterByClass(self::PARAM_BASE_CLASS_NAME, self::PARAM_VOTING, $voting);
    }

    public function isPpt(): bool
    {
        return $this->ppt;
    }

    public function setPpt(bool $ppt): void
    {
        $this->ppt = $ppt;

        self::dic()->ctrl()->setParameterByClass(self::PARAM_BASE_CLASS_NAME, self::PARAM_PPT, $ppt);
    }
}
