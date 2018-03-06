# TANet Auth
Place this app in **owncloud/apps/**

## Needs to add config.php
```
'hash_key' => hash('SHA384', 'Set your hash key',true),

'crc_key' => hash('SHA384', 'Set your crc key',true),

'tanet_login_url' => 'https://owncloud-ceph.com/tanetlogin.php',

'tanet_return_url_key' => '?returnUrl=',

'tanet_requests' => array (
    0 => '\\OCA\\Tanet_Auth\\GetInfo',
),

'tanet_global_logout' => true,

'tanet_admin_login_uri' => '/admin',

'tanet_admin_login_port' => 443,

'tanet_one_time_password' => true,

'radius_server' => 'Your radius server ip',

'radius_port' => 1812,

'radius_shared_secret' => 'Your radius server shared secret',
```


