<?php

namespace OCA\Tanet_Auth;

/**
 * Class UserInfoSetter
 * @author Dauba
 */
class UserInfoSetter
{
    /**
     * Set ownCloud user info
     *
     * @return void
     */
    public static function setInfo($user, $userInfo)
    {
        $config = \OC::$server->getConfig();
        $userID = $userInfo->getUserId();
        
        $regionData = \OC::$server->getConfig()->getUserValue($userID, "settings", "regionData",false);
        if (!$regionData){
            $data = ['region' => $userInfo->getRegion(),
                    'schoolCode' => 'undefined',
            ];
            $config->setUserValue($userID, "settings", "regionData", json_encode($data));
        }
        
        
        if ($config->getUserValue($userID, "settings", "role") != NULL) {
            return;
        }

        $config->setUserValue($userID, "files", "quota", "20 GB");
        $config->setUserValue($userID, "settings", "email", $userInfo->getEmail());
        $config->setUserValue($userID, "settings", "role", $userInfo->getRole());
        
    }

}

