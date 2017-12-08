<?php
namespace OCA\Tanet_Auth;

use Exception;

class TanetAuthProcessor {

    /**
     * required keys in config/config.php
     */
    private static $requiredKeys = array("tanet_login_url",
                                         "tanet_return_url_key",
                                         "tanet_requests",
                                         "tanet_global_logout",
                                         "sso_multiple_region",
                                         "tanet_admin_login_port",
                                         "tanet_admin_login_uri",
                                         "tanet_one_time_password");

    /**
     * uri which unnecessary authenticate with Single Sign-On
     */
    private static $unnecessaryAuthUri = array("(.*\/webdav.*)",
                                                "(.*\/cloud.*)",
                                                "(.*\/s\/.*)",
                                                "(\/admin)",
                                                "(.*\/ocs\/.*)",
                                                "(\/core\/js\/oc\.js)",
                                                "(\/apps\/gallery\/config\.public)",
                                                "(.*\/files_sharing\/ajax\/.*)",
                                                "(.*\/files_sharing\/shareinfo.*)",
                                                "(\/apps\/files_pdfviewer\/)",
                                                "(\/apps\/gallery\/.*)");

    /**
     * Necessary class
     *
     * @var array
     */
    private static $necessaryImplementationClass = array("\\OCA\\Tanet_Auth\\AuthInfo",
                                                  "\\OCA\\Tanet_Auth\\WebDavAuthInfo");

    /**
     * \OC\SystemConfig
     */
    private $config;

    /**
     * \OC\Appframework\Http\Request
     */
    private $request; 

    /**
     * user token
     */
    private $token;

    /**
     * url where to redirect after SSO login
     */
    private $redirectUrl;

    /**
     * user visit port on server
     *
     * @var int
     */
    private $visitPort;

    public function run() {
        try {
            $this->process();
        }catch (Exception $e){
            echo $e->getMessage();
        }
    }

    public function __construct() {
        $this->request = \OC::$server->getRequest();
        $this->config = \OC::$server->getSystemConfig();
        $this->redirectUrl = $this->request->getRequestUri();
        $this->defaultPageUrl = \OC_Util::getDefaultPageUrl();
        $this->visitPort = (int)$_SERVER["SERVER_PORT"];

        if($this->config->getValue("sso_multiple_region")) {
            array_push(self::$requiredKeys, "sso_owncloud_url");
            array_push(self::$requiredKeys, "sso_regions");
            array_push(self::$necessaryImplementationClass, "\\OCA\\Tanet_Auth\\RedirectRegion");
        }

        foreach(self::$necessaryImplementationClass as $class) {
            if(!class_exists($class)) {
                throw new Exception("The class " . $class . " did't exist.");
            }
        }

        self::checkKeyExist(self::$requiredKeys);

        RequestManager::init($this->config->getValue("tanet_requests"));
    }
    public function __destruct(){
        Util::clearEncryptFromDB();
    }

    public function process() {
        $ssoUrl = $this->config->getValue("tanet_login_url");
        $userInfo = RequestManager::getRequest(ITanetAuthRequest::INFO);
        $authInfo = AuthInfo::get();

        $userInfo->setup(array("action" => "webLogin"));

        if($this->unnecessaryAuth($this->request->getRequestUri())){
            $uri = substr($this->request->getRequestUri(), (-1)*strlen($this->config->getValue("tanet_admin_login_uri")));
            if ($uri === $this->config->getValue("tanet_admin_login_uri") && $this->visitPort != $this->config->getValue("tanet_admin_login_port")) {
                Util::redirect($this->defaultPageUrl);
            }
            return;
        }

        if(isset($_GET["logout"]) && $_GET["logout"] == "true") {
            \OC_User::logout();
            $template = new \OC_Template("tanet_auth", "logout", "guest");
            $template->printPage();
            die();
        }

        if(\OC_User::isLoggedIn()) {
            return ;
        }
        
        if(empty($ssoUrl) || !$userInfo->send($authInfo) || !$userInfo->hasPermission() || !$userInfo->getRegion()) {
            header("HTTP/1.1 " . \OCP\AppFramework\Http::STATUS_UNAUTHORIZED);
            header("Status: " . \OCP\AppFramework\Http::STATUS_UNAUTHORIZED);
            header("WWW-Authenticate: ");
            header("Retry-After: 120");

            $template = new \OC_Template("tanet_auth", "verificationFailure", "guest");
            $template->printPage();
            if($userInfo->hasErrorMsg()) {
                \OCP\Util::writeLog("TANet_Auth", $userInfo->getErrorMsg(), \OCP\Util::ERROR);
            }
            die();
        }
        
        
        
        if($this->config->getValue("sso_multiple_region")) {
            Util::redirectRegion($userInfo, $this->config->getValue("sso_regions"), $this->config->getValue("sso_owncloud_url"));
        }

        if(!\OC_User::userExists($userInfo->getUserId())) {
            \OC::$server->getSession()->set("LOGIN_TANET",true);
            Util::firstLogin($userInfo, $authInfo);
            Util::saveEncryptToDB($authInfo['encrypt'], $userInfo->getUserId(), $authInfo['time']);
            if($this->request->getHeader("ORIGIN")) {
                return;
            }
            Util::redirect($this->defaultPageUrl);
        }
        else {
            \OC::$server->getSession()->set("LOGIN_TANET",true);
            Util::login($userInfo, $authInfo);
            Util::saveEncryptToDB($authInfo['encrypt'], $userInfo->getUserId(), $authInfo['time']);
            if($this->request->getHeader("ORIGIN")) {
                return;
            }

            Util::redirect($this->defaultPageUrl);
        }
    }

    /**
     * Check key is exist or not in config/config.php
     *
     * @param array reqiured keys
     * @return void
     */
    public static function checkKeyExist($requiredKeys) {
        $configKeys = \OC::$server->getSystemConfig()->getKeys();

        foreach ($requiredKeys as $key) {
            if (!in_array($key, $configKeys)) {
                throw new Exception("The config key " . $key . " did't exist.");
            }
        }
    }

    /**
     * unnecessaryAuth
     * @param array url path
     * @param array uri
     * @return bool
     **/
    private function unnecessaryAuth($uri) {
        for ($i = 0; $i < count(self::$unnecessaryAuthUri); $i++) {
            if ($i == 0) {
                $NAUri = self::$unnecessaryAuthUri[$i];
            }
            else {
                $NAUri = $NAUri . "|" . self::$unnecessaryAuthUri[$i];
            }
        }

        $NAUri = "/" . $NAUri . "/";

        preg_match($NAUri, $uri, $matches);

        if(count($matches) || \OC_User::isAdminUser(\OC_User::getUser())){
            return true;
        }

        return false;
    }
    
    /**
     * Get TanetAuthProcessor.
     *
     * @return Object \OCA\TanetAuthProcessor
     */
    public static function getInstance() {
        return new static();
    }

    /**
     * Get the user token
     *
     * @return string user token
     */
    public function getToken() {
        return $this->token;
    }
}
