<?php
/**
 * StormRecon - worker.php.
 * User: simonbeattie
 * Date: 02/07/2014
 * Time: 22:03
 */

include_once('Library/bootstrap.php');


$queue =  new Pheanstalk_Pheanstalk('127.0.0.1:11300');

$worker = new \Library\Worker($pdo);

// Set which queues to bind to
$queue->watch("agent");

// pick a job and process it
while($job = $queue->reserve()) {
    $received = json_decode($job->getData(), true);

    print_r($received);

    $outcome = true;

        // how did it go?
        if($outcome) {
            echo "done \n";
            $queue->delete($job);
        } else {
            echo "failed \n";
            $queue->bury($job);
        }


//    } else {
//        echo "action not found\n";
//        $queue->bury($job);
//    }

}