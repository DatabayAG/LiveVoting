<?php

declare(strict_types=1);

namespace LiveVoting\GUI;

use ilLinkButton;
use ilLiveVotingPlugin;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

class xlvoLinkButton extends ilLinkButton
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    public const TYPE_XLVO_LINK = 'xlvo_link';

    public static function getInstance(): self
    {
        return new self(self::TYPE_XLVO_LINK);
    }

    protected function prepareRender(): void
    {
        $this->addCSSClass('btn');
    }

    public function clearClasses(): void
    {
        $this->css = [];
    }
}
