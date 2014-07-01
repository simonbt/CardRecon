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

include_once('Routes/profiles.php');
include_once('Routes/regex.php');
include_once('Routes/scans.php');
include_once('Routes/hosts.php');

$app->run();