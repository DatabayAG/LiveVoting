<?php

declare(strict_types=1);

namespace LiveVoting\Js;

use ilLiveVotingPlugin;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

class xlvoJsSettings
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    protected array $settings = [];
    protected array $translations = [];

    public function __construct()
    {
    }

    public function addSetting(string $name, $value): void
    {
        $this->settings[$name] = $value;
    }

    public function addTranslation(string $key): void
    {
        $this->translations[$key] = self::plugin()->translate($key);
    }

    public function asJson(): string
    {
        $arr = array();
        foreach ($this->settings as $name => $value) {
            $arr[$name] = $value;
        }

        foreach ($this->translations as $key => $string) {
            $arr['lng'][$key] = $string;
        }

        return json_encode($arr, JSON_THROW_ON_ERROR);
    }
}
