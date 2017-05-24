<?php

namespace OCA\Tanet_Auth;

/**
 * Interface IWebDavAuthInfo
 * @author Dauba
 */
interface IWebDavAuthInfo
{
    /**
     * get auth info
     *
     * @return array
     * @author Dauba
     */
    public static function get($userID, $password,$radiusServer,$radiusPort,$radiusSharedSecret);
}
