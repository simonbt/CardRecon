<?php
/**
 * StormRecon - index.php.
 * User: simonbeattie
 * Date: 30/06/2014
 * Time: 15:56
 */

include_once('Library/bootstrap.php');

$app = new \Slim\Slim(array(
    'templates.path' => './Views'
));

session_start();

include_once('Routes/API/profiles.php');
include_once('Routes/API/scans.php');
include_once('Routes/API/hosts.php');
include_once('Routes/API/win_agent.php');
include_once('Routes/Interface/menus.php');

$app->run();