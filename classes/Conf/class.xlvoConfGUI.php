<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use LiveVoting\Conf\xlvoConf;
use LiveVoting\Conf\xlvoConfFormGUI;
use LiveVoting\Context\Param\ParamManager;
use LiveVoting\GUI\xlvoGUI;
use LiveVoting\Pin\xlvoPin;
use LiveVoting\Voting\xlvoVoting;

/**
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @ilCtrl_IsCalledBy xlvoConfGUI : xlvoMainGUI
 */
class xlvoConfGUI extends xlvoGUI
{
    public const CMD_RESET_TOKEN = 'resetToken';

    public function txt(string $key): string
    {
        return self::plugin()->translate($key, 'config');
    }


    public function index(): void
    {
        if (xlvoConf::getConfig(xlvoConf::F_RESULT_API)) {
            $b = ilLinkButton::getInstance();
            $b->setUrl(self::dic()->ctrl()->getLinkTarget($this, self::CMD_RESET_TOKEN));
            $b->setCaption($this->txt('regenerate_token'), false);
            self::dic()->toolbar()->addButtonInstance($b);
            $b = ilLinkButton::getInstance();
            $xlvoVoting = xlvoVoting::last();
            $xlvoVoting = $xlvoVoting ?: new xlvoVoting();
            $url = xlvoConf::getBaseVoteURL() . xlvoConf::RESULT_API_URL . '?token=%s&type=%s&' . ParamManager::PARAM_PIN . '=%s';
            $url = sprintf($url, xlvoConf::getApiToken(), xlvoConf::getConfig(xlvoConf::F_API_TYPE), xlvoPin::lookupPin($xlvoVoting->getObjId()));
            $b->setUrl($url);
            $b->setTarget('_blank');
            $b->setCaption($this->txt('open_result_api'), false);
            self::dic()->toolbar()->addButtonInstance($b);
        }

        $xlvoConfFormGUI = new xlvoConfFormGUI($this);
        $xlvoConfFormGUI->fillForm();
        self::dic()->ui()->mainTemplate()->setContent($xlvoConfFormGUI->getHTML());
    }


    protected function resetToken() : void
    {
        xlvoConf::set(xlvoConf::F_API_TOKEN, null);
        xlvoConf::getConfig(xlvoConf::F_API_TOKEN);
        $this->cancel();
    }


    protected function update() : void
    {
        $xlvoConfFormGUI = new xlvoConfFormGUI($this);
        $xlvoConfFormGUI->setValuesByPost();
        if ($xlvoConfFormGUI->saveObject()) {
            $this->tpl->setO($this->txt('msg_success'), true);
            self::dic()->ctrl()->redirect($this, self::CMD_STANDARD);
        }
        self::dic()->ui()->mainTemplate()->setContent($xlvoConfFormGUI->getHTML());
    }


    protected function confirmDelete() : void
    {
    }


    protected function delete() : void
    {
    }


    protected function add(): void
    {
    }


    protected function create(): void
    {
    }


    protected function edit(): void
    {
    }
}
