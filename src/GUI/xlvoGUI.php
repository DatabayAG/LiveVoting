<?php

declare(strict_types=1);

namespace LiveVoting\GUI;

use ilLiveVotingPlugin;
use LiveVoting\Conf\xlvoConf;
use LiveVoting\Context\Param\ParamManager;
use LiveVoting\Js\xlvoJs;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;

abstract class xlvoGUI
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    public const CMD_STANDARD = 'index';
    public const CMD_ADD = 'add';
    public const CMD_SAVE = 'save';
    public const CMD_CREATE = 'create';
    public const CMD_EDIT = 'edit';
    public const CMD_UPDATE = 'update';
    public const CMD_CONFIRM = 'confirmDelete';
    public const CMD_DELETE = 'delete';
    public const CMD_CANCEL = 'cancel';
    public const CMD_VIEW = 'view';
    protected bool $is_api_call;
    protected ParamManager $param_manager;

    public function __construct()
    {
        $this->param_manager = ParamManager::getInstance();

        $this->is_api_call = (self::dic()->ctrl()->getTargetScript() === xlvoConf::getFullApiURL());

        self::dic()->ctrl()->saveParameter($this, "lang");
    }

    protected function cancel(): void
    {
        self::dic()->ctrl()->redirect($this, self::CMD_STANDARD);
    }

    public function executeCommand(): void
    {
        $nextClass = self::dic()->ctrl()->getNextClass();
        xlvoJs::getInstance()->name('Main')->init()->setRunCode();
        switch ($nextClass) {
            default:
                $cmd = self::dic()->ctrl()->getCmd(self::CMD_STANDARD);
                $this->{$cmd}();
                break;
        }
        if ($this->is_api_call) {
            if (self::version()->is6()) {
                self::dic()->ui()->mainTemplate()->fillJavaScriptFiles();
                self::dic()->ui()->mainTemplate()->fillCssFiles();
                self::dic()->ui()->mainTemplate()->fillOnLoadCode();
                self::dic()->ui()->mainTemplate()->printToStdout("", false, true);
            } else {
                self::dic()->ui()->mainTemplate()->show(false);
            }
        }
    }
}
