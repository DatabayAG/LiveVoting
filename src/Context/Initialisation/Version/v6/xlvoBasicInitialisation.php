<?php

declare(strict_types=1);

namespace LiveVoting\Context\Initialisation\Version\v6;

require_once './include/inc.ilias_version.php';

use Closure;
use Collator;
use ilAccess;
use ilAppEventHandler;
use ilBenchmark;
use ilCtrl;
use ilDBWrapperFactory;
use ilErrorHandling;
use ilGlobalCache;
use ilGlobalCacheSettings;
use ilGlobalTemplate;
use ilHelp;
use ilHTTPS;
use ILIAS\DI\Container;
use ILIAS\DI\HTTPServices;
use ILIAS\HTTP\Cookies\CookieJarFactoryImpl;
use ILIAS\HTTP\Request\RequestFactoryImpl;
use ILIAS\HTTP\Response\ResponseFactoryImpl;
use ILIAS\HTTP\Response\Sender\DefaultResponseSenderStrategy;
use ilIniFile;
use ilInitialisation;
use iljQueryUtil;
use ilLanguage;
use ilLiveVotingPlugin;
use ilLoggerFactory;
use ilMailMimeSenderFactory;
use ilMailMimeTransportFactory;
use ilMainMenuGUI;
use ilNavigationHistory;
use ilObjectDataCache;
use ilPluginAdmin;
use ilSetting;
use ilTabsGUI;
use ilTimeZone;
use ilToolbarGUI;
use ilTree;
use ilUIFramework;
use ilUtil;
use LiveVoting\Conf\xlvoConf;
use LiveVoting\Context\Param\ParamManager;
use LiveVoting\Context\xlvoContext;
use LiveVoting\Context\xlvoILIAS;
use LiveVoting\Context\xlvoObjectDefinition;
use LiveVoting\Context\xlvoRbacReview;
use LiveVoting\Context\xlvoRbacSystem;
use LiveVoting\Session\xlvoSessionHandler;
use LiveVoting\Utils\LiveVotingTrait;
use srag\DIC\LiveVoting\DICTrait;
use LiveVoting\Context\xlvoDummyUser6;
use ilException;
use ilFileUtils;

class xlvoBasicInitialisation
{
    use DICTrait;
    use LiveVotingTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;
    protected ilIniFile $iliasIniFile;
    protected ilSetting $settings;

    protected function __construct(int $context = null)
    {
        if ($context) {
            xlvoContext::setContext($context);
        }

        $this->bootstrapApp();
    }

    private function bootstrapApp(): void
    {
        //bootstrap ILIAS

        $this->initDependencyInjection();
        $this->setCookieParams();

        $this->removeUnsafeCharacters();
        $this->loadIniFile();
        $this->requireCommonIncludes();
        $this->initErrorHandling();
        $this->determineClient();
        $this->loadClientIniFile();
        $this->initDatabase();
        $this->initLog(); //<-- required for ilCtrl error messages
        $this->initSessionHandler();
        $this->initSettings();  //required
        $this->initAccessHandling();
        $this->buildHTTPPath();
        self::initHTTPServices();
        $this->initLocale();
        $this->initLanguage();
        $this->initDataCache();
        $this->initObjectDefinition();
        $this->initControllFlow();
        $this->initUser();
        $this->initPluginAdmin();
        $this->initAccess();
        $this->initTree();
        $this->initAppEventHandler();
        $this->initMail();
        $this->initFilesystem();
        $this->initGlobalScreen();
        $this->initTemplate();
        $this->initTabs();
        $this->initNavigationHistory();
        $this->initHelp();
        $this->initMainMenu();
    }

    private function initDependencyInjection(): void
    {
        global $DIC;
        require_once 'libs/composer/vendor/autoload.php';
        //			require_once 'src/DI/Container.php';
        $DIC = new Container();
        $DIC["ilLoggerFactory"] = static function ($c) {
            return ilLoggerFactory::getInstance();
        };
    }

    /**
     * set session cookie params for path, domain, etc.
     */
    private function setCookieParams(): void
    {
        $GLOBALS['COOKIE_PATH'] = '/';
        $cookie_path = '/';

        /* if ilias is called directly within the docroot $cookie_path
        is set to '/' expecting on servers running under windows..
        here it is set to '\'.
        in both cases a further '/' won't be appended due to the following regex
        */
        $cookie_path .= (!preg_match("/[\\/|\\\\]$/", $cookie_path)) ? "/" : "";

        if ($cookie_path === "\\") {
            $cookie_path = '/';
        }

        /*$cookie_secure = !$this->settings->get('https', 0) && ilHTTPS::getInstance()->isDetected();*/
        $cookie_secure = true;

        define('IL_COOKIE_EXPIRE', 0);
        define('IL_COOKIE_PATH', $cookie_path);
        define('IL_COOKIE_DOMAIN', '');
        define('IL_COOKIE_SECURE', $cookie_secure); // Default Value

        define('IL_COOKIE_HTTPONLY', true); // Default Value
        session_set_cookie_params(
            IL_COOKIE_EXPIRE,
            IL_COOKIE_PATH,
            IL_COOKIE_DOMAIN,
            IL_COOKIE_SECURE,
            IL_COOKIE_HTTPONLY
        );
    }

    protected function removeUnsafeCharacters(): void
    {
        // Remove unsafe characters from GET parameters.
        // We do not need this characters in any case, so it is
        // feasible to filter them everytime. POST parameters
        // need attention through ilUtil::stripSlashes() and similar functions)
        if (is_array($_GET)) {
            foreach ($_GET as $k => $v) {
                // \r\n used for IMAP MX Injection
                // ' used for SQL Injection
                $_GET[$k] = str_replace(array(
                    "\x00",
                    "\n",
                    "\r",
                    "\\",
                    "'",
                    '"',
                    "\x1a",
                ), "", $v);

                // this one is for XSS of any kind
                $_GET[$k] = strip_tags($_GET[$k]);
            }
        }
    }

    private function loadIniFile(): void
    {
        $this->iliasIniFile = new ilIniFile("./ilias.ini.php");
        $this->iliasIniFile->read();
        $this->makeGlobal('ilIliasIniFile', $this->iliasIniFile);

        // initialize constants
        define("ILIAS_DATA_DIR", $this->iliasIniFile->readVariable("clients", "datadir"));
        define("ILIAS_WEB_DIR", $this->iliasIniFile->readVariable("clients", "path"));
        define("ILIAS_ABSOLUTE_PATH", $this->iliasIniFile->readVariable('server', 'absolute_path'));

        // logging
        define("ILIAS_LOG_DIR", $this->iliasIniFile->readVariable("log", "path"));
        define("ILIAS_LOG_FILE", $this->iliasIniFile->readVariable("log", "file"));
        define("ILIAS_LOG_ENABLED", $this->iliasIniFile->readVariable("log", "enabled"));
        define("ILIAS_LOG_LEVEL", $this->iliasIniFile->readVariable("log", "level"));
        define("SLOW_REQUEST_TIME", $this->iliasIniFile->readVariable("log", "slow_request_time"));

        // read path + command for third party tools from ilias.ini
        define("PATH_TO_CONVERT", $this->iliasIniFile->readVariable("tools", "convert"));
        define("PATH_TO_FFMPEG", $this->iliasIniFile->readVariable("tools", "ffmpeg"));
        define("PATH_TO_ZIP", $this->iliasIniFile->readVariable("tools", "zip"));
        define("PATH_TO_MKISOFS", $this->iliasIniFile->readVariable("tools", "mkisofs"));
        define("PATH_TO_UNZIP", $this->iliasIniFile->readVariable("tools", "unzip"));
        define("PATH_TO_GHOSTSCRIPT", $this->iliasIniFile->readVariable("tools", "ghostscript"));
        define("PATH_TO_JAVA", $this->iliasIniFile->readVariable("tools", "java"));
        define("PATH_TO_HTMLDOC", $this->iliasIniFile->readVariable("tools", "htmldoc"));
        define("URL_TO_LATEX", $this->iliasIniFile->readVariable("tools", "latex"));
        define("PATH_TO_FOP", $this->iliasIniFile->readVariable("tools", "fop"));

        // read virus scanner settings
        switch ($this->iliasIniFile->readVariable("tools", "vscantype")) {
            case "sophos":
                define("IL_VIRUS_SCANNER", "Sophos");
                define("IL_VIRUS_SCAN_COMMAND", $this->iliasIniFile->readVariable("tools", "scancommand"));
                define("IL_VIRUS_CLEAN_COMMAND", $this->iliasIniFile->readVariable("tools", "cleancommand"));
                break;

            case "antivir":
                define("IL_VIRUS_SCANNER", "AntiVir");
                define("IL_VIRUS_SCAN_COMMAND", $this->iliasIniFile->readVariable("tools", "scancommand"));
                define("IL_VIRUS_CLEAN_COMMAND", $this->iliasIniFile->readVariable("tools", "cleancommand"));
                break;

            case "clamav":
                define("IL_VIRUS_SCANNER", "ClamAV");
                define("IL_VIRUS_SCAN_COMMAND", $this->iliasIniFile->readVariable("tools", "scancommand"));
                define("IL_VIRUS_CLEAN_COMMAND", $this->iliasIniFile->readVariable("tools", "cleancommand"));
                break;

            default:
                define("IL_VIRUS_SCANNER", "None");
                break;
        }

        $tz = ilTimeZone::initDefaultTimeZone($this->iliasIniFile);
        define("IL_TIMEZONE", $tz);
        define('IL_INITIAL_WD', getcwd());
    }

    private function makeGlobal(string $name, object $value): void
    {
        global $DIC;
        $GLOBALS[$name] = $value;
        $DIC[$name] = static function ($c) use ($name) {
            return $GLOBALS[$name];
        };
    }

    private function requireCommonIncludes(): void
    {
        // really always required?
        //		require_once 'Services/Utilities/classes/class.ilFormat.php';
        require_once 'include/inc.ilias_version.php';

        $this->makeGlobal("ilBench", new ilBenchmark());
    }

    private function initErrorHandling(): void
    {
        // error_reporting(((ini_get("error_reporting")) & ~E_DEPRECATED) & ~E_STRICT); // removed reading ini since notices lead to a non working livevoting in 5.2 when E_NOTICE is enabled
        error_reporting(E_ALL&~E_DEPRECATED&~E_STRICT&~E_NOTICE);

        $this->requireCommonIncludes();

        // error handler
        if (!defined('ERROR_HANDLER')) {
            define('ERROR_HANDLER', 'PRETTY_PAGE');
        }
        if (!defined('DEVMODE')) {
            define('DEVMODE', false);
        }

        require_once "./libs/composer/vendor/filp/whoops/src/Whoops/Util/SystemFacade.php";
        require_once "./libs/composer/vendor/filp/whoops/src/Whoops/RunInterface.php";
        require_once "./libs/composer/vendor/filp/whoops/src/Whoops/Run.php";
        require_once "./libs/composer/vendor/filp/whoops/src/Whoops/Handler/HandlerInterface.php";
        require_once "./libs/composer/vendor/filp/whoops/src/Whoops/Handler/Handler.php";
        require_once "./libs/composer/vendor/filp/whoops/src/Whoops/Handler/CallbackHandler.php";

        require_once "./Services/Init/classes/class.ilErrorHandling.php";
        $ilErr = new ilErrorHandling();
        $this->makeGlobal("ilErr", $ilErr);
        $ilErr::setErrorHandling(PEAR_ERROR_CALLBACK, array($ilErr, 'errorHandler'));
    }

    private function determineClient(): void
    {
        // check whether ini file object exists
        if (!is_object($this->iliasIniFile)) {
            throw new ilException(
                "Fatal Error: ilInitialisation::determineClient called without initialisation of ILIAS ini file object."
            );
        }

        // set to default client if empty
        if ($_GET["client_id"] !== "") {
            $_GET["client_id"] = ilUtil::stripSlashes($_GET["client_id"]);
            if (!defined("IL_PHPUNIT_TEST")) {
                ilUtil::setCookie("ilClientId", $_GET["client_id"]);
            }
        } elseif (!$_COOKIE["ilClientId"]) {
            // to do: ilias ini raus nehmen
            $client_id = $this->iliasIniFile->readVariable("clients", "default");
            ilUtil::setCookie("ilClientId", $client_id);
        }
        if (!defined("IL_PHPUNIT_TEST")) {
            define("CLIENT_ID", $_COOKIE["ilClientId"]);
        } else {
            define("CLIENT_ID", $_GET["client_id"]);
        }
    }

    private function loadClientIniFile(): bool
    {
        $ini_file = "./" . ILIAS_WEB_DIR . "/" . CLIENT_ID . "/client.ini.php";

        // get settings from ini file
        $ilClientIniFile = new ilIniFile($ini_file);
        $ilClientIniFile->read();

        // invalid client id / client ini
        if ($ilClientIniFile->ERROR !== "") {
            $default_client = $this->iliasIniFile->readVariable("clients", "default");
            ilUtil::setCookie("ilClientId", $default_client);
        }

        $this->makeGlobal("ilClientIniFile", $ilClientIniFile);

        // set constants
        define("SESSION_REMINDER_LEADTIME", 30);
        define("DEBUG", $ilClientIniFile->readVariable("system", "DEBUG"));
        define("DEVMODE", $ilClientIniFile->readVariable("system", "DEVMODE"));
        define("SHOWNOTICES", $ilClientIniFile->readVariable("system", "SHOWNOTICES"));
        define("DEBUGTOOLS", $ilClientIniFile->readVariable("system", "DEBUGTOOLS"));
        define("ROOT_FOLDER_ID", $ilClientIniFile->readVariable('system', 'ROOT_FOLDER_ID'));
        define("SYSTEM_FOLDER_ID", $ilClientIniFile->readVariable('system', 'SYSTEM_FOLDER_ID'));
        define("ROLE_FOLDER_ID", $ilClientIniFile->readVariable('system', 'ROLE_FOLDER_ID'));
        define("MAIL_SETTINGS_ID", $ilClientIniFile->readVariable('system', 'MAIL_SETTINGS_ID'));
        $error_handler = $ilClientIniFile->readVariable('system', 'ERROR_HANDLER');
        define("ERROR_HANDLER", $error_handler ?: "PRETTY_PAGE");
        $log_error_trace = $ilClientIniFile->readVariable('system', 'LOG_ERROR_TRACE');
        define("LOG_ERROR_TRACE", $log_error_trace ?: false);

        // this is for the online help installation, which sets OH_REF_ID to the
        // ref id of the online module
        define("OH_REF_ID", $ilClientIniFile->readVariable("system", "OH_REF_ID"));

        define("SYSTEM_MAIL_ADDRESS", $ilClientIniFile->readVariable('system', 'MAIL_SENT_ADDRESS')); // Change SS
        define("MAIL_REPLY_WARNING", $ilClientIniFile->readVariable('system', 'MAIL_REPLY_WARNING')); // Change SS

        define("CLIENT_DATA_DIR", ILIAS_DATA_DIR . "/" . CLIENT_ID);
        define("CLIENT_WEB_DIR", ILIAS_ABSOLUTE_PATH . "/" . ILIAS_WEB_DIR . "/" . CLIENT_ID);
        define("CLIENT_NAME", $ilClientIniFile->readVariable('client', 'name')); // Change SS

        $val = $ilClientIniFile->readVariable("db", "type");
        if ($val === "") {
            define("IL_DB_TYPE", "mysql");
        } else {
            define("IL_DB_TYPE", $val);
        }

        $ilGlobalCacheSettings = new ilGlobalCacheSettings();
        $ilGlobalCacheSettings->readFromIniFile($ilClientIniFile);
        ilGlobalCache::setup($ilGlobalCacheSettings);

        return true;
    }

    private function initDatabase(): void
    {
        // build dsn of database connection and connect
        $ilDB = ilDBWrapperFactory::getWrapper(IL_DB_TYPE);
        $ilDB->initFromIniFile();
        $ilDB->connect();

        $this->makeGlobal("ilDB", $ilDB);
    }

    private function initLog(): void
    {
        $log = ilLoggerFactory::getRootLogger();

        $this->makeGlobal("ilLog", $log);
        // deprecated
        $this->makeGlobal("log", $log);
    }

    private function initSessionHandler(): void
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

        session_start();
    }

    private function initSettings(): void
    {
        $this->settings = new ilSetting();
        $this->makeGlobal("ilSetting", $this->settings);

        // set anonymous user & role id and system role id
        define("ANONYMOUS_USER_ID", $this->settings->get("anonymous_user_id"));
        define("ANONYMOUS_ROLE_ID", $this->settings->get("anonymous_role_id"));
        define("SYSTEM_USER_ID", $this->settings->get("system_user_id"));
        define("SYSTEM_ROLE_ID", $this->settings->get("system_role_id"));
        define("USER_FOLDER_ID", 7);

        // recovery folder
        define("RECOVERY_FOLDER_ID", $this->settings->get("recovery_folder_id"));

        // installation id
        define("IL_INST_ID", $this->settings->get("inst_id", ''));

        // define default suffix replacements
        define("SUFFIX_REPL_DEFAULT", "php,php3,php4,inc,lang,phtml,htaccess");
        define("SUFFIX_REPL_ADDITIONAL", $this->settings->get("suffix_repl_additional"));

        // payment setting
        define('IS_PAYMENT_ENABLED', false);
    }

    /**
     * Starting from ILIAS 5.2 basic initialisation also needs rbac stuff.
     * You may ask why? well: deep down ilias wants to initialize the footer. Event hough we don't
     * want the footer. This may not seem too bad... but the footer wants to translate something
     * and the translation somehow needs rbac. god...
     *
     * We can remove this when this gets fixed: Services/UICore/classes/class.ilTemplate.php:479
     */
    private function initAccessHandling(): void
    {
        // thisone we can mock
        $this->makeGlobal('rbacreview', new xlvoRbacReview());

        $rbacsystem = new xlvoRbacSystem();
        $this->makeGlobal("rbacsystem", $rbacsystem);
    }

    private function buildHTTPPath(): void
    {
        $https = new ilHTTPS();
        $this->makeGlobal("https", $https);

        if ($https->isDetected()) {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }
        $host = $_SERVER['HTTP_HOST'];

        $rq_uri = $_SERVER['REQUEST_URI'];

        // security fix: this failed, if the URI contained "?" and following "/"
        // -> we remove everything after "?"
        if (is_int($pos = strpos($rq_uri, "?"))) {
            $rq_uri = substr($rq_uri, 0, $pos);
        }

        if (!defined('ILIAS_MODULE')) {
            $path = pathinfo($rq_uri);
            if (!$path['extension']) {
                $uri = $rq_uri;
            } else {
                $uri = dirname($rq_uri);
            }
        } else {
            // if in module remove module name from HTTP_PATH
            $path = dirname($rq_uri);

            // dirname cuts the last directory from a directory path e.g content/classes return content

            $module = ilFileUtils::removeTrailingPathSeparators(ILIAS_MODULE);

            $dirs = explode('/', $module);
            $uri = $path;
            foreach ($dirs as $dir) {
                $uri = dirname($uri);
            }
        }

        $https->enableSecureCookies();
        $https->checkPort();

        define('ILIAS_HTTP_PATH', ilFileUtils::removeTrailingPathSeparators($protocol . $host . $uri));
    }

    /**
     * Initialise a fake http services to satisfy the help system module.
     */
    private static function initHTTPServices(): void
    {
        global $DIC;

        $DIC['http.request_factory'] = static function ($c) {
            return new RequestFactoryImpl();
        };

        $DIC['http.response_factory'] = static function ($c) {
            return new ResponseFactoryImpl();
        };

        $DIC['http.cookie_jar_factory'] = static function ($c) {
            return new CookieJarFactoryImpl();
        };

        $DIC['http.response_sender_strategy'] = static function ($c) {
            return new DefaultResponseSenderStrategy();
        };

        $DIC['http'] = function ($c) {
            return new HTTPServices(
                $c['http.response_sender_strategy'],
                $c['http.cookie_jar_factory'],
                $c['http.request_factory'],
                $c['http.response_factory']
            );
        };
    }

    private function initLocale(): void
    {
        if (trim($this->settings->get("locale")) !== "") {
            $larr = explode(",", trim($this->settings->get("locale")));
            $ls = array();
            $first = $larr[0];
            foreach ($larr as $l) {
                if (trim($l) !== "") {
                    $ls[] = $l;
                }
            }
            if (count($ls) > 0) {
                setlocale(LC_ALL, $ls);

                // #15347 - making sure that floats are not changed
                setlocale(LC_NUMERIC, "C");

                if (class_exists("Collator")) {
                    $this->makeGlobal("ilCollator", new Collator($first));
                }
            }
        }
    }

    private function initLanguage(): void
    {
        $this->makeGlobal('lng', ilLanguage::getGlobalInstance());
    }

    private function initDataCache(): void
    {
        $this->makeGlobal("ilObjDataCache", new ilObjectDataCache());
    }

    private function initObjectDefinition(): void
    {
        $this->makeGlobal("objDefinition", new xlvoObjectDefinition());
    }

    private function initControllFlow(): void
    {
        $this->makeGlobal("ilCtrl", new ilCtrl());
    }

    private function initUser(): void
    {
        $this->makeGlobal('ilUser', new xlvoDummyUser6());
    }

    private function initPluginAdmin(): void
    {
        $this->makeGlobal("ilPluginAdmin", new ilPluginAdmin());
    }

    private function initAccess(): void
    {
        $this->makeGlobal('ilAccess', new ilAccess());
    }

    private function initTree(): void
    {
        $this->makeGlobal('tree', new ilTree(ROOT_FOLDER_ID));
    }

    private function initAppEventHandler(): void
    {
        $this->makeGlobal("ilAppEventHandler", new ilAppEventHandler());
    }

    private function initMail(): void
    {
        $this->makeGlobal(
            "mail.mime.transport.factory",
            new ilMailMimeTransportFactory(self::dic()->settings(), self::dic()->appEventHandler())
        );

        $this->makeGlobal("mail.mime.sender.factory", new ilMailMimeSenderFactory(self::dic()->settings()));
    }

    private function initFilesystem(): void
    {
        Closure::bind(function () {
            self::bootstrapFilesystems();
        }, null, ilInitialisation::class)();
    }

    public function initGlobalScreen(): void
    {
        Closure::bind(static function (Container $dic) {
            self::initGlobalScreen($dic);
        }, null, ilInitialisation::class)(
            self::dic()->dic()
        );
    }

    private function initTemplate(): void
    {
        $styleDefinition = new xlvoStyleDefinition();
        $this->makeGlobal('styleDefinition', $styleDefinition);

        $ilias = new xlvoILIAS();
        $this->makeGlobal("ilias", $ilias);

        $tpl = new ilGlobalTemplate(
            "tpl.main.html",
            true,
            true,
            'Customizing/global/plugins/Services/Repository/RepositoryObject/LiveVoting',
            "DEFAULT",
            true
        );

        $param_manager = ParamManager::getInstance();
        //$tpl = self::plugin()->template("default/tpl.main.html");
        if (!$param_manager->getPuk()) {
            $tpl->touchBlock("navbar");
        }

        $tpl->addCss('./templates/default/delos.css');
        $tpl->addCss(self::plugin()->directory() . '/templates/default/default.css');

        $tpl->addBlockFile(
            "CONTENT",
            "content",
            "tpl.main_voter.html",
            'Customizing/global/plugins/Services/Repository/RepositoryObject/LiveVoting'
        );
        $tpl->setVariable('BASE', xlvoConf::getBaseVoteURL());
        $this->makeGlobal("tpl", $tpl);

        iljQueryUtil::initjQuery();
        ilUIFramework::init();

        $ilToolbar = new ilToolbarGUI();
        $this->makeGlobal("ilToolbar", $ilToolbar);
    }

    /**
     * Initialise a fake tabs service to satisfy the help system module.
     */
    private function initTabs(): void
    {
        $this->makeGlobal('ilTabs', new ilTabsGUI());
    }

    /**
     * Initialise a fake NavigationHistory service to satisfy the help system module.
     */
    private function initNavigationHistory(): void
    {
        $this->makeGlobal('ilNavigationHistory', new ilNavigationHistory());
    }

    /**
     * Initialise a fake help service to satisfy the help system module.
     */
    private function initHelp(): void
    {
        $this->makeGlobal('ilHelp', new ilHelp());
    }

    /**
     * Initialise a fake MainMenu service to satisfy the help system module.
     */
    private function initMainMenu(): void
    {
        $this->makeGlobal('ilMainMenu', new ilMainMenuGUI());
    }

    /**
     * @param int $context
     *
     * @return xlvoBasicInitialisation
     */
    public static function init(int $context = null): xlvoBasicInitialisation
    {
        return new self($context);
    }
}
