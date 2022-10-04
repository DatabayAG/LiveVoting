<?php

declare(strict_types=1);

namespace LiveVoting\Js;

use ilLiveVotingPlugin;
use LiveVoting\Conf\xlvoConf;
use LiveVoting\Context\Param\ParamManager;
use LiveVoting\GUI\xlvoGUI;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;
use ilSetting;
use ilMathJax;

class xlvoJs
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    public const DEVELOP = false;
    public const API_URL = xlvoConf::API_URL;
    public const BASE_URL_SETTING = 'base_url';
    public const BASE_PATH = './Customizing/global/plugins/Services/Repository/RepositoryObject/LiveVoting/js/';
    protected string $class_name = '';
    protected string $setting_class_name = '';
    protected bool $init = false;
    protected string $lib = '';
    protected string $name = '';
    protected string $category = '';
    protected xlvoJsSettings $settings;

    protected function __construct()
    {
        $this->settings = new xlvoJsSettings();
    }

    public static function getInstance(): self
    {
        return new self();
    }

    public function addSettings(array $settings): self
    {
        foreach ($settings as $k => $v) {
            $this->settings->addSetting($k, $v);
        }

        return $this;
    }

    public function addTranslations(array $translations): self
    {
        foreach ($translations as $translation) {
            $this->settings->addTranslation($translation);
        }

        return $this;
    }

    public function api(xlvoGUI $xlvoGUI, array $additional_classes = [], string $cmd = ''): xlvoJs
    {
        $ilCtrl2 = clone(self::dic()->ctrl());
        //self::dic()->ctrl()->initBaseClass(ilUIPluginRouterGUI::class);
        $ilCtrl2->setTargetScript(self::API_URL);
        $additional_classes[] = get_class($xlvoGUI);

        ParamManager::getInstance();

        $this->settings->addSetting(
            self::BASE_URL_SETTING,
            self::dic()->ctrl()->getLinkTargetByClass($additional_classes, $cmd, null, true)
        );

        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function category(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function ilias(xlvoGUI $xlvoGUI, string $cmd = ''): self
    {
        $this->settings->addSetting(
            self::BASE_URL_SETTING,
            self::dic()->ctrl()->getLinkTarget($xlvoGUI, $cmd, '', true)
        );

        return $this;
    }

    public function getLibraryURL(): string
    {
        $this->resolveLib();

        return $this->lib;
    }

    protected function resolveLib(): void
    {
        $base_path = self::BASE_PATH;
        $category = ($this->category ? $this->category . '/' : '') . $this->name . '/';
        $file_name = ilLiveVotingPlugin::PLUGIN_ID . $this->name . '.js';
        $file_name_min = ilLiveVotingPlugin::PLUGIN_ID . $this->name . '.min.js';
        $full_path_min = $base_path . $category . $file_name_min;
        $full_path = $base_path . $category . $file_name;
        if (!self::DEVELOP && is_file($full_path_min)) {
            $this->lib = $full_path_min;
        } else {
            $this->lib = $full_path;
        }
    }

    public function init(): self
    {
        $this->init = true;
        $this->resolveLib();
        $this->addLibToHeader($this->lib, false);
        $this->setInitCode();

        return $this;
    }

    public function addLibToHeader(string $name_of_lib, bool $external = true): self
    {
        if ($external) {
            self::dic()->ui()->mainTemplate()->addJavascript(self::plugin()->directory() . '/js/libs/' . $name_of_lib);
        } else {
            self::dic()->ui()->mainTemplate()->addJavaScript($name_of_lib);
        }

        return $this;
    }

    public function setInitCode(): self
    {
        return $this->call("init", $this->settings->asJson());
    }

    public function call(string $method, string $params = ''): self
    {
        if (!$this->init) {
            return $this;
        }
        $this->addOnLoadCode($this->getCallCode($method, $params));

        return $this;
    }

    public function addOnLoadCode(string $code): self
    {
        self::dic()->ui()->mainTemplate()->addOnLoadCode($code);

        return $this;
    }

    public function getCallCode(string $method, string $params = ''): string
    {
        return ilLiveVotingPlugin::PLUGIN_ID . $this->name . '.' . $method . '(' . $params . ');';
    }

    public function getRunCode(): string
    {
        return '<script>' . $this->getCallCode("run") . '</script>';
    }

    public function setRunCode(): self
    {
        return $this->call("run");
    }

    public function initMathJax(): void
    {
        $mathJaxSetting = new ilSetting("MathJax");
        if (strpos(
            $mathJaxSetting->get('path_to_mathjax'),
            'mathjax@3'
        ) !== false) { // not sure if this check will work with >v3
            // mathjax v3 needs to be configured differently
            $this->addLibToHeader('mathjax_config.js');
        }
        ilMathJax::getInstance()->includeMathJax();
    }
}
