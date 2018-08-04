<?php
umask(0000); // This will let the permissions be 0777

/**
 * Application front controller for `dev` environment.
 *
 * @copyright (c) 2016 Tomasz Chojna
 * @link http://epi.chojna.info.pl
 */

ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.
if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', 'fe80::1', '::1'))
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

require_once dirname(dirname(__FILE__)).'/vendor/autoload.php';

$app = require_once dirname(dirname(__FILE__)).'/src/app.php';

$app['debug'] = true;

require_once dirname(dirname(__FILE__)).'/src/controllers.php';

$app->run();
