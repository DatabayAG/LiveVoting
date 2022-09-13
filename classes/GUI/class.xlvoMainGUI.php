<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use LiveVoting\GUI\xlvoGUI;

/**
 * Class xlvoMainGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @version           1.0.0
 *
 * @ilCtrl_IsCalledBy xlvoMainGUI : ilLiveVotingConfigGUI
 */
class xlvoMainGUI extends xlvoGUI
{
    public const TAB_SETTINGS = 'settings';
    public const TAB_SYSTEM_ACCOUNTS = 'system_accounts';
    public const TAB_PUBLICATION_USAGE = 'publication_usage';
    public const TAB_EXPORT = 'export';


    /**
     * @return void
     */
    public function executeCommand(): void
    {
        $nextClass = self::dic()->ctrl()->getNextClass();
        switch ($nextClass) {
            default:
                $xlvoConfGUI = new xlvoConfGUI();
                self::dic()->ctrl()->forwardCommand($xlvoConfGUI);
                break;
        }
    }
}
