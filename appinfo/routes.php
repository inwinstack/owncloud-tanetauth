<?php
/**
 * ownCloud - testmiddleware
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Duncan Chiang <duncan.c@inwinstack.com>
 * @copyright 2017 inwinSTACK.Inc
 */

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\TestMiddleWare\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
namespace OCA\Tanet_Auth\AppInfo;

$application = new Application();

$application->registerRoutes($this,[
        'routes' => [
        [
			'name'         => 'tanetauth_api#api', // Valid for all API end points
			'url'          => '/api/{path}',
			'verb'         => 'OPTIONS',
			'requirements' => ['path' => '.+']
		], 
       ['name' => 'tanetauth_api#index', 'url' => '/api/test', 'verb' => 'GET'],
    ]
]);


\OCP\API::register(
        'get',
        '/apps/tanet_auth/checkNeedRedirect',
        function($urlParameters) {
            $userid = $_GET['userid'];
            $userinfo = new \OCA\Tanet_Auth\GetInfo();
            $region = $userinfo->filterRegion($userid);
            $result = false;
            $host = null;
            if($region){
                $config = \OC::$server->getSystemConfig();
                if($config->getValue("sso_multiple_region")) {
                    $request = \OC::$server->getRequest();
                    
                    $serverUrls = $config->getValue("sso_owncloud_url");
                    $regions = $config->getValue("sso_regions");
                    
                    if($request->getServerHost() !== $serverUrls[$regions[$region]]) {
                        $result = true;
                        $host = $serverUrls[$regions[$region]];
                    }
                }
            }
            else{
                $host = false;
            }
            $data = array('result'=> $result,'host'=> $host);
            return new \OC_OCS_Result($data);
        },
        'tanet_auth',
        \OC_API::GUEST_AUTH);
