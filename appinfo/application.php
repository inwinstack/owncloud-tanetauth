<?php
/**
 * ownCloud - tanet_auth
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Duncan Chiang <duncan.c@inwinstack.com>
 * @copyright 2017 inwinSTACK.Inc
 */

namespace OCA\Tanet_Auth\AppInfo;

use OCP\AppFramework\App;
use OCA\Tanet_Auth\Middleware\TanetAuthMiddleware; 

class Application extends App {
    /**
     * Define your dependencies in here
     */
    public function __construct(array $urlParams=array()){
        parent::__construct('tanet_auth', $urlParams);

        $container = $this->getContainer();

        /**
         * Middleware
         */
        $container->registerService('TanetAuthMiddleware', function($c) {
			return new TanetAuthMiddleware(
				$c['Request'],
				$c['ControllerMethodReflector'],
				$c['OCP\IUserSession']
			);
		});
        // executed in the order that it is registered
        $container->registerMiddleware('TanetAuthMiddleware');
    }
}
