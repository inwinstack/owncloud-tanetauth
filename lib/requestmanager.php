<?php
namespace OCA\Tanet_Auth;

use Exception;

class RequestManager {
    private static $requests = array();

    public static function init($requests) {

        foreach($requests as $request) {
            if(!class_exists($request)) {
                throw new Exception("The class " . $request . " did't exist.");
            }
        }

        foreach($requests as $request) {
            $request = new $request();
            if($request instanceof ITanetAuthRequest) {
                self::$requests[$request->name()] = $request;
            }
        }
    }

    public static function send($requestName, $data = array()) {
        if(array_key_exists($requestName, self::$requests)) {
            return self::$requests[$requestName]->send($data);
        }
        return false;
    }

    public static function getRequest($requestName) {
        if(array_key_exists($requestName, self::$requests)) {
            return self::$requests[$requestName];
        }
        return false;
    }
}
