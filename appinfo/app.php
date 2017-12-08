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

$app = new App('tanet_auth');
$application = new Application();
$container = $app->getContainer();

$container->registerService("L10N", function($c) {
    return $c->getServerContainer()->getL10N("tanet_auth");
});

$request = \OC::$server->getRequest();

if($request->offsetGet("tanet")) {
    \OC::$server->getSession()->set("LOGIN_TANET",true);
    $processor = new \OCA\Tanet_Auth\TanetAuthProcessor();
    $processor->run();
}

\OCP\Util::addScript("tanet_auth", "script");
