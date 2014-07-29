<?php
/**
 * StormRecon - deployment_worker.php.
 * User: simonbeattie
 * Date: 02/07/2014
 * Time: 22:50
 */

include_once(__DIR__ . '/../Library/bootstrap.php');

$startTime = time();
$pidLocation = '/tmp/agent_worker.pid';
$pid = getmypid();
$allowedRunTime = $config['worker']['runtime'];


echo 'I am ' . $pid . '.' . PHP_EOL;

if(file_exists($pidLocation))
{
    $oldPid = trim(file_get_contents($pidLocation));

    echo 'The old process is pid: ' . $oldPid . PHP_EOL;

    if(file_exists("/proc/$oldPid"))
    {
        die('The old process is still running for pid file: ' . $pidLocation . PHP_EOL);
    }
}
file_put_contents($pidLocation, $pid);



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
            $worker->checkSuccess($result, $job);
            if(time() - $startTime > $allowedRunTime) { die('I\'m done for the day :)'); }
            break;
        case 2:
            $scanner = new \Library\ExistingAgent($received['profile_id'], $pdo, $received['host_id'], $queue, $logger);
            $result = $scanner->startService();
            $worker->checkSuccess($result, $job);
            if(time() - $startTime > $allowedRunTime) { die('I\'m done for the day :)'); }
            break;
        case 3:
            $scanner = new \Library\ExistingAgent($received['profile_id'], $pdo, $received['host_id'], $queue, $logger);
            $result = $scanner->stopService();
            $worker->checkSuccess($result, $job);
            if(time() - $startTime > $allowedRunTime) { die('I\'m done for the day :)'); }
            break;
        case 4:
            $scanner = new \Library\ExistingAgent($received['profile_id'], $pdo, $received['host_id'], $queue, $logger);
            $result = $scanner->killAgent();
            $worker->checkSuccess($result, $job);
            if(time() - $startTime > $allowedRunTime) { die('I\'m done for the day :)'); }
            break;
        default:
            break;
    }
}
