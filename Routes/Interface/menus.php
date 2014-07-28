<?php
/**
 * StormRecon - menus.php.
 * User: simonbeattie
 * Date: 03/07/2014
 * Time: 12:13
 */

$app->get('/', function() use($app)
{
    $app->render('menus/index.phtml', array());
});