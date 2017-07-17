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
            $info = Util::decryptHash($request->offsetGet("encrypt"));
            if (!$info || time() - $info['time'] > 600 || $request->getRemoteAddress() != $info['ip']){
                    return null;
            }
            self::$info['userid'] = $info['userid'];
            self::$info['password'] = $info['password'];
            self::$info['encrypt'] = $request->offsetGet("encrypt");
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


