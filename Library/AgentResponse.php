<?php
/**
 * StormRecon - AgentResponse.php.
 * User: simonbeattie
 * Date: 02/07/2014
 * Time: 09:05
 */

namespace Library;

class AgentResponse extends ReconAbstract{

    private $queue;

    function __construct($pdo)
    {
        parent::__construct($pdo);
         $this->queue = new \Pheanstalk_Pheanstalk('127.0.0.1:11300');
    }

    public function receive($postData, $fileData)
    {

        if (array_key_exists('results', $fileData))
        {
            $resultsFileContent = file_get_contents($fileData['results']['tmp_name']);
            $resultsLine = explode("\n", $resultsFileContent);
            foreach ($resultsLine as $result)
            {
                $this->addResult(explode("\t", $result), $postData['tracker']);
            }

        }

        if (array_key_exists('log', $fileData))
        {
            $logFileContent = file_get_contents($fileData['log']['tmp_name']);
            //file_put_contents('/tmp/log.log', print_r($logFileContent, true), FILE_APPEND);

        }

        //file_put_contents('/tmp/post.log', print_r($postData, true), FILE_APPEND);

        /*
         * Job Actions
         * 1 - Update host name
         * 2 - Update host totals
         * 3 - Update host progress
         * 4 - Host completed
         */

        if (array_key_exists('status', $postData))
        {
            switch($postData['status'])
            {
                case 0 :
                    $job = array(
                        "action"        =>  '1',
                        "host_name"     =>  $postData['hostname'],
                        "start_time"    =>  date('Y-m-d H:i:s'),
                        "status"        =>  '3',
                        "tracker"       =>  $postData['tracker']
                    );
                    $this->queue->useTube('agent')->put(json_encode($job));
                    $this->updateHostName($postData['hostname'], $postData['tracker']);
                    break;
                case 1 :
                    $job = array(
                        "action"        =>  '2',
                        "bytestotal"    =>  $postData['bytestotal'],
                        "filestotal"    =>  $postData['filestotal'],
                        "status"        =>  '3',
                        "tracker"       =>  $postData['tracker']
                    );
                    $this->queue->useTube('agent')->put(json_encode($job));
                    $this->updateHostTotals($postData['bytestotal'], $postData['filestotal'], $postData['tracker']);
                    break;
                case 2 :
                    $job = array(
                        "action"        =>  '3',
                        "bytesscanned"  =>  $postData['bytesscanned'],
                        "filesscanned"  =>  $postData['filesscanned'],
                        "tracker"       =>  $postData['tracker']
                    );
                    $this->queue->useTube('agent')->put(json_encode($job));
                    $this->updateHostProgress($postData['bytesscanned'], $postData['filesscanned'], $postData['tracker']);
                    break;
                case 3 :
                    $job = array(
                        "action"        =>  '4',
                        "bytesscanned"  =>  $postData['bytesscanned'],
                        "filesscanned"  =>  $postData['filesscanned'],
                        "tracker"       =>  $postData['tracker'],
                        "profile"       =>  $postData['profile']
                    );
                    $this->queue->useTube('agent')->put(json_encode($job));
                    $this->hostCompleted($postData['bytesscanned'], $postData['filesscanned'], $postData['tracker'], $postData['profile']);
                    break;
            }
        }
    }

    private function uninstallAgent($profileID, $pdo, $hostID)
    {
        $scanner = new \Library\ExistingAgent($profileID[0], $pdo, $hostID[0]);
        $scanner->killAgent();
    }

    private function hostCompleted($bytesS, $filesS, $tracker, $profile)
    {
        $date = date('Y-m-d H:i:s');
        $updateProgress = $this->getPdo()->prepare('UPDATE hosts SET bytesscanned =?, filesscanned =?, end_time =?, status =4 WHERE tracker =?');
        $updateProgress->execute(array($bytesS, $filesS, $date , $tracker));

        $getHostID = $this->getPdo()->prepare('SELECT id FROM hosts WHERE tracker =?');
        $getHostID->execute(array($tracker));
        $hostID = $getHostID->fetchAll(\PDO::FETCH_COLUMN);

        $getProfileID = $this->getPdo()->prepare('SELECT id FROM profiles WHERE profile_name =?');
        $getProfileID->execute(array($profile));
        $profileID = $getProfileID->fetchAll(\PDO::FETCH_COLUMN);

        $this->uninstallAgent($profileID, $this->getPdo(), $hostID);
    }

    private function updateHostProgress($bytesS, $filesS, $tracker)
    {
        $updateProgress = $this->getPdo()->prepare('UPDATE hosts SET bytesscanned =?, filesscanned =? WHERE tracker =?');
        $updateProgress->execute(array($bytesS, $filesS, $tracker));
    }

    private function updateHostTotals($bytesT, $filesT, $tracker)
    {
        $updateTotals = $this->getPdo()->prepare('UPDATE hosts SET bytestotal =?, filestotal =?, status =3 WHERE tracker =?');
        $updateTotals->execute(array($bytesT, $filesT, $tracker));

    }

    private function updateHostName($hostname, $tracker)
    {
        $date = date('Y-m-d H:i:s');
        $updateName = $this->getPdo()->prepare('UPDATE hosts SET host_name =?, start_time =?, status =2 WHERE tracker =?');
        $updateName->execute(array($hostname, $date, $tracker));

    }

    private function addResult($result, $tracker)
    {
        $resultsQuery = $this->getPdo()->prepare('INSERT INTO results (tracker, filename, regex_name, result, offset, md5, zipfile) VALUES (?, ?, ?, ?, ?, ?, ?)');
        if (count($result) > 1)
        {
            array_unshift($result, $tracker);
            $success = $resultsQuery->execute(array_pad($result, 7, null));

            if (!$success)
            {
                die('Failed to add result - ' . print_r($resultsQuery->errorInfo()));
            }
        }
    }
} 