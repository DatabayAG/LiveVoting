<?php

declare(strict_types=1);

namespace LiveVoting\GUI;

use ilLinkButton;
use ilLiveVotingPlugin;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

/**
 * Class xlvoLinkButton
 *
 * @package LiveVoting\GUI
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class xlvoLinkButton extends ilLinkButton
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    public const TYPE_XLVO_LINK = 'xlvo_link';

    /**
     * @return xlvoLinkButton
     */
    public static function getInstance()
    {
        return new self(self::TYPE_XLVO_LINK);
    }

    /**
     * Prepare render
     */
    protected function prepareRender()
    {
        $this->addCSSClass('btn');
    }

    public function clearClasses()
    {
        $this->css = array();
    }
}
