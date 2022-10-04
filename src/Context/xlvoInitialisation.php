<?php

declare(strict_types=1);

namespace LiveVoting\Context;

use Exception;
use ILIAS\DI\Container;
use ilInitialisation;
use iljQueryUtil;
use ilLiveVotingPlugin;
use ilTree;
use ilUIFramework;
use LiveVoting\Conf\xlvoConf;
use LiveVoting\Session\xlvoSessionHandler;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;
use ilContext;
use ilGlobalPageTemplate;
use ilUserRequestTargetAdjustment;
use ilMainMenuGUI;
use ilSession;
use ilGlobalTemplate;
use ilLoggerFactory;

/**
 * Initializes a ILIAS environment depending on Context (PIN or ILIAS).
 * This is used in every entry-point for users and AJAX requests
 *
 */
class xlvoInitialisation extends ilInitialisation
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    public const USE_OWN_GLOBAL_TPL = true;
    public const CONTEXT_PIN = 1;
    public const CONTEXT_ILIAS = 2;
    protected static ilTree $tree;
    protected static int $context = self::CONTEXT_PIN;

    protected function __construct(int $context = null)
    {
        if ($context) {
            self::saveContext($context);
        } else {
            self::setContext(xlvoContext::getContext());
        }
        $this->run();
    }

    /**
     * @throws Exception
     */
    public static function saveContext(int $context): void
    {
        self::setContext($context);
        xlvoContext::setContext($context);
    }

    protected function run(): void
    {
        //		$this->setContext(self::CONTEXT_ILIAS);
        switch (self::getContext()) {
            case self::CONTEXT_ILIAS:
                require_once 'include/inc.header.php';
                self::initHTML2();
                //				self::initILIAS();
                break;
            case self::CONTEXT_PIN:
                xlvoContext::init(xlvoContextLiveVoting::class);
                self::initILIAS2();
                break;
        }
    }

    public static function getContext(): int
    {
        return self::$context;
    }

    public static function setContext(int $context): void
    {
        self::$context = $context;
    }

    protected static function initHTML2(): void
    {
        global $DIC;
        if ($DIC->offsetExists("tpl")) {
            $DIC->offsetUnset("tpl");
        }
        if ($DIC->offsetExists("ilNavigationHistory")) {
            $DIC->offsetUnset("ilNavigationHistory");
        }
        if ($DIC->offsetExists("ilHelp")) {
            $DIC->offsetUnset("ilHelp");
        }
        if ($DIC->offsetExists("styleDefinition")) {
            $DIC->offsetUnset("styleDefinition");
        }
        self::initHTML();
        if (self::USE_OWN_GLOBAL_TPL) {
            if (self::version()->is6()) {
                $tpl = new ilGlobalTemplate(
                    "tpl.main.html",
                    true,
                    true,
                    'Customizing/global/plugins/Services/Repository/RepositoryObject/LiveVoting',
                    "DEFAULT",
                    true
                );
            } else {
                $tpl = self::plugin()->template("default/tpl.main.html");
            }
            $tpl->touchBlock("navbar");
            $tpl->addCss('./templates/default/delos.css');
            $tpl->addBlockFile("CONTENT", "content", "tpl.main_voter.html", self::plugin()->directory());

            if ($DIC->offsetExists("tpl")) {
                $DIC->offsetUnset("tpl");
            }

            self::initGlobal("tpl", $tpl);
        }
        if (!self::USE_OWN_GLOBAL_TPL) {
            $tpl->getStandardTemplate();
        }

        $tpl->setVariable('BASE', xlvoConf::getBaseVoteURL());
        if (self::USE_OWN_GLOBAL_TPL) {
            iljQueryUtil::initjQuery();
            ilUIFramework::init();
        }
    }

    protected static function initHTML(): void
    {
        if (!self::version()->is6()) {
            parent::initHTML();
            return;
        }
        // copied parent function
        global $ilUser, $DIC;

        if (ilContext::hasUser()) {
            // load style definitions
            // use the init function with plugin hook here, too
            self::initStyle();
        }

        self::initUIFramework($GLOBALS["DIC"]);
        $tpl = new ilGlobalPageTemplate($DIC->globalScreen(), $DIC->ui(), $DIC->http());
        self::initGlobal("tpl", $tpl);

        if (ilContext::hasUser()) {
            if (self::version()->is7()) {
                $dispatcher = new \ILIAS\Init\StartupSequence\StartUpSequenceDispatcher($DIC);
                $dispatcher->dispatch();
            } else {
                $request_adjuster = new ilUserRequestTargetAdjustment(
                    $ilUser,
                    $GLOBALS['DIC']['ilCtrl'],
                    $GLOBALS['DIC']->http()->request()
                );
                $request_adjuster->adjust();
            }
        }

        require_once "./Services/UICore/classes/class.ilFrameTargetInfo.php";

        self::initGlobal(
            "ilNavigationHistory",
            "ilNavigationHistory",
            "Services/Navigation/classes/class.ilNavigationHistory.php"
        );

        self::initGlobal(
            "ilBrowser",
            "ilBrowser",
            "./Services/Utilities/classes/class.ilBrowser.php"
        );

        self::initGlobal(
            "ilHelp",
            "ilHelpGUI",
            "Services/Help/classes/class.ilHelpGUI.php"
        );

        self::initGlobal(
            "ilToolbar",
            "ilToolbarGUI",
            "./Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php"
        );

        self::initGlobal(
            "ilLocator",
            "ilLocatorGUI",
            "./Services/Locator/classes/class.ilLocatorGUI.php"
        );

        self::initGlobal(
            "ilTabs",
            "ilTabsGUI",
            "./Services/UIComponent/Tabs/classes/class.ilTabsGUI.php"
        );

        if (ilContext::hasUser()) {
            include_once './Services/MainMenu/classes/class.ilMainMenuGUI.php';
            $ilMainMenu = new ilMainMenuGUI("_top");

            self::initGlobal("ilMainMenu", $ilMainMenu);
            unset($ilMainMenu);

            // :TODO: tableGUI related

            // set hits per page for all lists using table module
            $_GET['limit'] = (int) $ilUser->getPref('hits_per_page');
            ilSession::set('tbl_limit', $_GET['limit']);

            // the next line makes it impossible to save the offset somehow in a session for
            // a specific table (I tried it for the user administration).
            // its not posssible to distinguish whether it has been set to page 1 (=offset = 0)
            // or not set at all (then we want the last offset, e.g. being used from a session var).
            // So I added the wrapping if statement. Seems to work (hopefully).
            // Alex April 14th 2006
            if (isset($_GET['offset']) && $_GET['offset'] !== "") {                            // added April 14th 2006
                $_GET['offset'] = (int) $_GET['offset'];        // old code
            }

        // leads to error in live voting
//            self::initGlobal("lti", "ilLTIViewGUI", "./Services/LTI/classes/class.ilLTIViewGUI.php");
//            $GLOBALS["DIC"]["lti"]->init();
//            self::initKioskMode($GLOBALS["DIC"]);
        } else {
            // several code parts rely on ilObjUser being always included
            include_once "Services/User/classes/class.ilObjUser.php";
        }
    }

    public static function initUIFramework(Container $c): void
    {
        parent::initUIFramework($c);
        parent::initRefinery($c);
    }

    /**
     * @param string $a_name
     * @param string|object $a_class
     * @param null   $a_source_file
     */
    protected static function initGlobal($a_name, $a_class, $a_source_file = null): void
    {
        global $DIC;

        if ($DIC->offsetExists($a_name)) {
            $DIC->offsetUnset($a_name);
        }

        parent::initGlobal($a_name, $a_class, $a_source_file);
    }

    /**
     *
     */
    public static function initILIAS2(): void
    {
        global $DIC;
        require_once 'include/inc.ilias_version.php';
        self::initDependencyInjection();
        self::initCore();
        self::initClient();
        self::initUser();
        self::initLanguage();
        self::$tree->initLangCode();
        self::initHTML2();
        $GLOBALS["objDefinition"] = $DIC["objDefinition"] = new xlvoObjectDefinition();
    }

    /**
     *
     */
    public static function initDependencyInjection(): void
    {
        global $DIC;
        $DIC = new Container();
        $DIC["ilLoggerFactory"] = static function ($c) {
            return ilLoggerFactory::getInstance();
        };
    }

    protected static function initClient(): void
    {
        self::determineClient();
        self::initClientIniFile();
        self::initDatabase();
        if (!is_object($GLOBALS["ilPluginAdmin"])) {
            self::initGlobal("ilPluginAdmin", "ilPluginAdmin", "./Services/Component/classes/class.ilPluginAdmin.php");
        }
        self::setSessionHandler();
        self::initSettings();
        self::initLocale();

        //		if (ilContext::usesHTTP()) {
        //			self::initGlobal("https", "ilHTTPS", "./Services/Http/classes/class.ilHTTPS.php");
        //			$https->enableSecureParams();
        //			$https->checkPort();
        //		}

        self::initGlobal(
            "ilObjDataCache",
            "ilObjectDataCache",
            "./Services/Object/classes/class.ilObjectDataCache.php"
        );
        self::$tree = new ilTree(ROOT_FOLDER_ID);
        self::initGlobal("tree", self::$tree);
        //unset(self::$tree);
        self::initGlobal("ilCtrl", "ilCtrl", "./Services/UICore/classes/class.ilCtrl.php");
        $GLOBALS['COOKIE_PATH'] = '/';
        self::setParamParams();
        self::initLog();
    }

    public static function setSessionHandler(): void
    {
        $session = new xlvoSessionHandler();

        session_set_save_handler([
            &$session,
            "open",
        ], [
            &$session,
            "close",
        ], [
            &$session,
            "read",
        ], [
            &$session,
            "write",
        ], [
            &$session,
            "destroy",
        ], [
            &$session,
            "gc",
        ]);
    }

    public static function init(int $context = null): xlvoInitialisation
    {
        return new self($context);
    }
}
