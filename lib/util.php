<?php
namespace OCA\Tanet_Auth;

use Exception;

class Util {
    
    public static function login($userInfo, $authInfo) {
        $userID = $userInfo->getUserId();
        $userToken = $userInfo->getToken();
        $manager = \OC::$server->getUserManager();


        $user = $manager->get($userID);
        \OC::$server->getUserSession()->setUser($user);
        \OC::$server->getUserSession()->setLoginName($user);
        \OC_Util::setupFS($userID);
        \OC::$server->getUserFolder($userID);

        if (class_exists('\\OCA\\Tanet_Auth\\UserInfoSetter')) {
            UserInfoSetter::setInfo($user, $userInfo);
        }
        $manager->emit('\OC\User', 'postLogin', array($user, $userToken));
        self::wirteAuthInfoToSession($authInfo);

        return true;
    }

    public static function firstLogin($userInfo, $authInfo) {
        $userID = $userInfo->getUserId();
        $password = RequestManager::getRequest(ITanetAuthRequest::USERPASSWORDGENERATOR) ? RequestManager::send(ISingleSignOnRequest::USERPASSWORDGENERATOR) : $userID;

        $user = \OC_User::createUser($userID, $password);

        if (class_exists('\\OCA\\Tanet_Auth\\UserInfoSetter')) {
            UserInfoSetter::setInfo($user, $userInfo);
        }

        self::wirteAuthInfoToSession($authInfo);
        return \OC_User::login($userID, $password);
    }

    public static function webDavLogin($userID, $password) {
        $config = \OC::$server->getSystemConfig();

        RequestManager::init($config->getValue("tanet_requests"));
        
        if (!$config->getValue("radius_server",false) ||
            !$config->getValue("radius_port",false) ||
            !$config->getValue("radius_shared_secret",false)){
            \OCP\Util::writeLog('tanet_auth','The radius_server, radius_port or radius_shared_secret not defined in config.php', \OCP\Util::INFO);
            return false;
        
        }

        $radiusServer = $config->getValue("radius_server");
        $radiusPort = $config->getValue("radius_port");
        $radiusSharedSecret = $config->getValue("radius_shared_secret");
        
        $authInfo = WebDavAuthInfo::get($userID, $password,$radiusServer,$radiusPort,$radiusSharedSecret);

        $userInfo = RequestManager::getRequest(ITanetAuthRequest::INFO);

        $userInfo->setup(array("action" => "webDavLogin"));

        if(!$userInfo->send($authInfo) || !$userInfo->getRegion() ) {
            return ;
        }
        
        if($config->getValue("sso_multiple_region")) {
            self::redirectRegion($userInfo, $config->getValue("sso_regions"), $config->getValue("sso_owncloud_url"));
        }
        
        if(!\OC_User::userExists($userInfo->getUserId())) {
            return self::firstLogin($userInfo, $authInfo);
        }

        if($authInfo){
            return self::login($userInfo, $authInfo);
        }

        return false;
    }

    public static function redirect($url) {
        if(!$url) {
            \OC_Util::redirectToDefaultPage();
        }
        else {
            header("location: " . $url);
            exit();
        }
    }

    /**
     * Check user region and redirect to correct region.
     *
     * @return void
     */
    public static function redirectRegion($userInfo, $regions, $serverUrls) {
        $region = $userInfo->getRegion();
        $request = \OC::$server->getRequest();

        if($request->getServerHost() === $serverUrls[$regions[$region]]) {
            return ;
        }

        $redirectUrl = RedirectRegion::getRegionUrl($region);

        self::redirect($redirectUrl);
    }

    /**
     * Write auth info to session
     *
     * @param array $authInfo
     * @return void
     */
    public static function wirteAuthInfoToSession($authInfo)
    {
        foreach ($authInfo as $key => $value) {
            \OC::$server->getSession()->set("tanet_" . $key, $value);
        }
    }

    /**
     * Decrypt account info hash
     *
     * @param string $encryptHash
     * @return array
     */
    public static function decryptHash($encryptHash)
    {
        $tanet_key = \OC::$server->getSystemConfig()->getValue("hash_key");
        $encrypt_account = base64_decode($encryptHash);
        $hash = hash('SHA384', $tanet_key, true);
        $app_cc_aes_key = substr($hash, 0, 32);
        $app_cc_aes_iv = substr($hash, 32, 16);
        
        $accountInfo = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $app_cc_aes_key, $encrypt_account, MCRYPT_MODE_CBC, $app_cc_aes_iv);
        $pieces = explode("&", $accountInfo);
        return array('userid' => trim($pieces[0]),
                     'password' => trim($pieces[1]),
                     'time' => isset($pieces[2]) ? trim($pieces[2]) : 0,
                    );
    }
}
