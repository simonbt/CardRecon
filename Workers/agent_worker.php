<?php
/**
 * StormRecon - agent_worker.php.
 * User: simonbeattie
 * Date: 02/07/2014
 * Time: 22:03
 */

include_once(__DIR__ . '/../Library/bootstrap.php');

$queue =  new Pheanstalk_Pheanstalk('127.0.0.1:11300');
$worker = new \Library\Worker($pdo);

// Set which queues to bind to
$queue->watch("agent");

// pick a job and process it
while($job = $queue->reserve()) {
    $received = json_decode($job->getData(), true);

    /*
     * Job Actions
     * 1 - Update host name
     * 2 - Update host totals
     * 3 - Update host progress
     * 4 - Host completed
     * 5 - Add result
     */

    switch($received['action'])
    {
        case 1:
            echo "Got update hostname Job\n";
            $result = $worker->updateHostName($received['hostname'], $received['tracker']);
            $worker->checkSuccess($result, $queue, $job);
            break;
        case 2:
            echo "Got update host totals job\n";
            $result = $worker->updateHostTotals($received['bytestotal'], $received['filestotal'], $received['tracker']);
            $worker->checkSuccess($result, $queue, $job);
            break;
        case 3:
            echo "Got update host progress job\n";
            $result = $worker->updateHostProgress($received['bytesscanned'], $received['filesscanned'], $received['tracker']);
            $worker->checkSuccess($result, $queue, $job);
            break;
        case 4:
            echo "Got update host completion job\n";
            $result = $worker->hostCompleted($received['bytesscanned'], $received['filesscanned'], $received['tracker'], $received['profile']);
            $worker->checkSuccess($result, $queue, $job);
            break;
        case 5:
            echo "Got add results job\n";
            $result = $worker->addResult($received['result'], $received['tracker']);
            $worker->checkSuccess($result, $queue, $job);
            break;
        default:
            echo "action not found\n";
            $queue->bury($job);
            break;
    }
}