<?php

namespace OCA\Tanet_Auth;

/**
 * Class AuthInfo
 * @author Dauba
 */
class AuthInfo implements IAuthInfo
{
    /**
     * requeir keys for auth info
     *
     * @var array
     */
    public static $requireKeys = array("userid","password");

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
    public static function get()
    {
        $request = \OC::$server->getRequest();
        $session = \OC::$server->getSession();

        if ($request->offsetGet("encrypt")) {
            $tanet_key = \OC::$server->getSystemConfig()->getValue("hash_key");
            $encrypt_account = base64_decode($request->offsetGet("encrypt"));
            $hash = hash('SHA384', $tanet_key, true);
            $app_cc_aes_key = substr($hash, 0, 32);
            $app_cc_aes_iv = substr($hash, 32, 16);

            $accountInfo = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $app_cc_aes_key, $encrypt_account, MCRYPT_MODE_CBC, $app_cc_aes_iv);
            $pieces = explode("&", $accountInfo);
            self::$info['userid'] = trim($pieces[0]);
            self::$info['password'] = trim($pieces[1]);
        }

        foreach (self::$requireKeys as $key) {
            if($request->offsetGet($key)) {
                self::$info[$key] = $request->offsetGet($key);
            }
            else if($request->getHeader($key)) {
                self::$info[$key] = $request->getHeader($key);
            }
            else if($session->get("tanet_" . $key)) {
                self::$info[$key] = $session->get("tanet_" . $key);
            }
        }

        self::$info["userIp"] = $request->getRemoteAddress();
        self::$info["tanet"] = 1;
        foreach (self::$requireKeys as $key) {
            if(!array_key_exists($key, self::$info)) {
                return null;
            }
        }

        return self::$info;
    }
    
}

