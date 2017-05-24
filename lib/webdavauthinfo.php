<?php

namespace OCA\Tanet_Auth;

/**
 * Class WebDavAuthInfo
 * @author Dauba
 */
class WebDavAuthInfo implements IWebDavAuthInfo
{
    
    /**
    * requeir keys for auth info
    *
    * @var array
    **/
    private static $requireKeys = array("userid", "password","radius_server","radius_port","radius_shared_secret");

    /**
     * auth info
     *
     * @var array
     */
    private static $info = array();
    
    /**
     * Getter for Info
     *
     * @return array
     */
    public static function get($userID, $password,$radiusServer,$radiusPort,$radiusSharedSecret)
    {
        self::$info["userid"] = $userID;
        self::$info["password"] = $password;
        self::$info["radius_server"] = $radiusServer;
        self::$info["radius_port"] = $radiusPort;
        self::$info["radius_shared_secret"] = $radiusSharedSecret;
        
        foreach (AuthInfo::$requireKeys as $key) {
            if(!array_key_exists($key, self::$info)) {
                return null;
            }
        }
        return self::$info;
    }
    
}
