<?php
/**
 * StormRecon - deployment_worker.php.
 * User: simonbeattie
 * Date: 02/07/2014
 * Time: 22:50
 */

include_once(__DIR__ . '/../Library/bootstrap.php');

$queue =  new Pheanstalk_Pheanstalk('127.0.0.1:11300');
$worker = new \Library\Worker($pdo, $queue, $logger);

// Set which queues to bind to
$queue->watch("deployment");

// pick a job and process it
while($job = $queue->reserve()) {
    $received = json_decode($job->getData(), true);

    /*
     * Job Actions
     * 1 - Deploy Agent
     * 2 - Start Agent
     * 3 - Stop Agent
     * 4 - Remove Agent
     */

    switch($received['action'])
    {
        case 1:
            $scanner = new \Library\NewAgent($pdo, $received['profile_id'], $received['scan_name'], $received['ip_address'], $queue, $logger);
            $result = $scanner->deployAgent();
            $worker->checkSuccess($result, $queue, $job);
            break;
        case 2:
            $scanner = new \Library\ExistingAgent($received['profile_id'], $pdo, $received['host_id'], $queue, $logger);
            $result = $scanner->startService();
            $worker->checkSuccess($result, $queue, $job);
            break;
        case 3:
            $scanner = new \Library\ExistingAgent($received['profile_id'], $pdo, $received['host_id'], $queue, $logger);
            $result = $scanner->stopService();
            $worker->checkSuccess($result, $queue, $job);
            break;
        case 4:
            $scanner = new \Library\ExistingAgent($received['profile_id'], $pdo, $received['host_id'], $queue, $logger);
            $result = $scanner->killAgent();
            $worker->checkSuccess($result, $queue, $job);
            break;
        default:
            break;
    }
}
