<?php
/**
 * StormRecon - start_scan.php.
 * User: simonbeattie
 * Date: 01/07/2014
 * Time: 13:25
 */

include_once('Library/bootstrap.php');

//Set profile to use
$scanner = new \Library\ExistingAgent('1', $pdo, '1', $queue, $logger);


//Deploy agent and start scan
$scanner->killAgent();
