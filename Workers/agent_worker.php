<?php
/**
 * StormRecon - agent_worker.php.
 * User: simonbeattie
 * Date: 02/07/2014
 * Time: 22:03
 */

include_once(__DIR__ . '/../Library/bootstrap.php');

$worker = new \Library\Worker($pdo, $queue, $logger);

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
            $worker->getLogger()->info('Got update hostname Job', array('job_id' => $job->getId()));
            $result = $worker->updateHostName($received['host_name'], $received['tracker']);
            $worker->checkSuccess($result, $job);
            break;
        case 2:
            $worker->getLogger()->info('Got update host totals job', array('job_id' => $job->getId()));
            $result = $worker->updateHostTotals($received['bytestotal'], $received['filestotal'], $received['tracker']);
            $worker->checkSuccess($result, $job);
            break;
        case 3:
            $worker->getLogger()->info('Got update host progress job', array('job_id' => $job->getId()));
            $result = $worker->updateHostProgress($received['bytesscanned'], $received['filesscanned'], $received['tracker']);
            $worker->checkSuccess($result, $job);
            break;
        case 4:
            $worker->getLogger()->info('Got update host completion job', array('job_id' => $job->getId()));
            $result = $worker->hostCompleted($queue, $received['bytesscanned'], $received['filesscanned'], $received['tracker'], $received['profile']);
            $worker->checkSuccess($result, $job);
            break;
        case 5:
            $worker->getLogger()->info('Got add results job', array('job_id' => $job->getId()));
            $result = $worker->addResult($received['result'], $received['tracker']);
            $worker->checkSuccess($result, $job);
            break;
        default:
            $worker->getLogger()->warning('action not found - removing job', array('job_id' => $job->getId()));
            $queue->bury($job);
            break;
    }
}