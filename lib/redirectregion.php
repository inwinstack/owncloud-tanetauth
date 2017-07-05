<?php
namespace OCA\Tanet_Auth;

class RedirectRegion implements IRedirectRegion{
    public static function getRegionUrl($region) {
        $request = \OC::$server->getRequest();
        $requestUri = $request->getRequestUri();
        $config = \OC::$server->getSystemConfig();
        $regions = $config->getValue("sso_regions");
        preg_match("/(.*\/ocs\/.*)|(.*\/webdav.*)/", $requestUri, $matches);

        if(count($matches)){
            $url = $request->getServerProtocol() . "://" . $config->getValue("sso_owncloud_url")[$regions[$region]] . $requestUri;
        }
        else {
            $authInfo = AuthInfo::get();
            $url = array('tanet' => 1,
                'encrypt' => $authInfo['encrypt'],
            );
            $url = $request->getServerProtocol() . "://" . $config->getValue("sso_owncloud_url")[$regions[$region]] . "?" . http_build_query($url);
        }
        return $url;
    }
}

