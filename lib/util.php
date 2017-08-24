<?php
namespace OCA\Tanet_Auth;

use Exception;

class Util {
    const  ENCRYPT_TTL = 600; 
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
            if ($key !== 'password'){
                \OC::$server->getSession()->set("tanet_" . $key, $value);
            }
        }
    }
    
    /**
     * Decrypt account info hash
     *
     * @param string $encryptHash
     * @param string $check
     * @return array/boolean
     */
    public static function decryptHash($encryptHash,$check)
    {
 
        // consider user's account(64),password(255),ipv6 and time length
        if (strlen($encryptHash) > 556){
            return false;
        }

        $hashKey = \OC::$server->getSystemConfig()->getValue("hash_key");
        $crcKey = \OC::$server->getSystemConfig()->getValue("crc_key");
 
        //Check md5 is the same
        if (md5($encryptHash . $crcKey) !== $check){
            return false;
        }
        
        $encryptAccount = base64_decode($encryptHash);
        $hash = hash('SHA384', $hashKey, true);
        $aesKey = substr($hash, 0, 32);
        $aesIv = substr($hash, 32, 16);
        
        $accountInfo = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $aesKey, $encryptAccount, MCRYPT_MODE_CBC, $aesIv);
        if ($accountInfo){
            $accountInfoArray = json_decode(trim($accountInfo),true);
            return $accountInfoArray;
        }
        return false;

    }

    /**
     * Check whether exist encrypt hash by userid
     *
     * @param string $encryptHash
     * @param string $userid
     * @return boolean
     */
    public static function checkEncryptExist($encryptHash,$userid)
    {
        $sql = 'SELECT * FROM *PREFIX*tanetauth_encrypt
                WHERE `encrypt` = ? AND `userid` = ?';
        $prepare = \OC_DB::prepare($sql);
        $result = $prepare->execute(array(md5($encryptHash),$userid));
        if ($result->rowCount() <= 0){
            return false;
        }
        return true;
    
    }
    
    /**
     * save encrypt hash by userid
     *
     * @param string $encryptHash
     * @param string $userid
     * @param integer $ttl
     * @return boolean
     */
    public static function saveEncryptToDB($encryptHash,$userid,$ttl)
    {
        $sql = "INSERT INTO *PREFIX*tanetauth_encrypt (`encrypt`, `userid`, `ttl`) VALUES (?, ?, ?)";
        $prepare = \OC_DB::prepare($sql);
        $result = $prepare->execute(array(md5($encryptHash),$userid,$ttl));
        if($result){
            return true;
        }
        return false;
    }
    
    /**
     * clear encrypt hash when > 600s
     *
     * @return boolean
     */
    public static function clearEncryptFromDB()
    {
        $sql = "DELETE FROM *PREFIX*tanetauth_encrypt WHERE (SELECT UNIX_TIMESTAMP()) - `ttl` > ?";
        $prepare = \OC_DB::prepare($sql);
        $result = $prepare->execute(array(self::ENCRYPT_TTL));
        if($result){
            return true;
        }
        return false;
    }    
}

