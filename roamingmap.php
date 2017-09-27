<?php
namespace OCA\Tanet_Auth;
class RoamingMap{
    /**
       in config.php, if you set 'sso_multiple_region' => true,
       you need set 
           'sso_regions' =>
              array (
                  "TANet_North" => "north",
           ),
           'sso_owncloud_url' =>
              array (
                  'north' => 'owncloud-ceph.com',
              ), 
    **/
    public static $map = array( "/mcu\.edu\.tw/" => 'TANet_North',
                                "/mail\.moe.gov\.tw/" => 'TANet_North',
                                "/0963091366@itw/" => 'TANet_South',
                                "/sammy@tn.edu.tw/" => 'TANet_South',
                                "/mail\.ncku\.edu\.tw/" => 'TANet_South',
                                "/nccu\.edu\.tw/" => 'TANet_North',
    );
}
