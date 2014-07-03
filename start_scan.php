<?php
/**
 * StormRecon - start_scan.php.
 * User: simonbeattie
 * Date: 01/07/2014
 * Time: 13:25
 */

include_once('Library/bootstrap.php');

//Set profile to use
$scanner = new \Library\NewAgent($pdo, '1', 'ScanName1', '192.168.200.250', $queue, $logger);


//Deploy agent and start scan
$scanner->deployAgent();

