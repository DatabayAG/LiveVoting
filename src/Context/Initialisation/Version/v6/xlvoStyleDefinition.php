<?php

declare(strict_types=1);

namespace LiveVoting\Context\Initialisation\Version\v6;

use ilLiveVotingPlugin;
use ilSkinStyleXML;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

class xlvoStyleDefinition
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    protected xlvoSkin $skin;

    /**
     * xlvoStyleDefinition constructor.
     */
    public function __construct()
    {
        $this->skin = new xlvoSkin();
    }

    public function getSkin(): xlvoSkin
    {
        return $this->skin;
    }

    public function getImageDirectory($style_id): string
    {
        return '';
    }
}

class xlvoSkin
{
    use DICTrait;

    public function hasStyle(): bool
    {
        return false;
    }

    public function getDefaultStyle(): ilSkinStyleXML
    {
        // required with ilias 5.4
        return new ilSkinStyleXML($this->getId(), $this->getName());
    }

    public function getId(): string
    {
        return 'delos';
    }

    public function getName(): string
    {
        return 'Delos';
    }
}
