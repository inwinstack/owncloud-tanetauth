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
    public static $requireKeys = array("userid","password","encrypt");

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
            $encrypt = $request->offsetGet("encrypt");
            $info = Util::decryptHash($encrypt);

            if (!$info || time() - $info['time'] > Util::ENCRYPT_TTL ||
                $request->getRemoteAddress() != $info['ip']){
                    return null;
            }
            if(Util::checkEncryptExist($encrypt,$info['userid'])){
                return null;
            }

            self::$info['userid'] = $info['userid'];
            self::$info['password'] = $info['password'];
            self::$info['encrypt'] = $encrypt;
            self::$info['time'] = $info['time'];
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


