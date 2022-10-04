<?php

declare(strict_types=1);

namespace LiveVoting\Context\Initialisation\Version\v6;

use ilGlobalTemplate;

class GlobalTemplate extends ilGlobalTemplate
{
    public function renderPage($part, $a_fill_tabs, $a_skip_main_menu, \ILIAS\DI\Container $DIC): string
    {
        return parent::renderPage($part, $a_fill_tabs, $a_skip_main_menu, $DIC);
    }
}
