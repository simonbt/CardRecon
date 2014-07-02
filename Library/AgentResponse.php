<?php
/**
 * StormRecon - AgentResponse.php.
 * User: simonbeattie
 * Date: 02/07/2014
 * Time: 09:05
 */

namespace Library;

class AgentResponse extends ReconAbstract{

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

            //file_put_contents('/tmp/results.log', print_r($resultsFileContent, true), FILE_APPEND);

        }

        if (array_key_exists('log', $fileData))
        {
            $logFileContent = file_get_contents($fileData['log']['tmp_name']);
            //file_put_contents('/tmp/log.log', print_r($logFileContent, true), FILE_APPEND);

        }

        //file_put_contents('/tmp/post.log', print_r($postData, true), FILE_APPEND);

        if (array_key_exists('status', $postData))
        {
            switch($postData['status'])
            {
                case 0 :
                    $this->updateHostName($postData['hostname'], $postData['tracker']);
                    break;
                case 1 :
                    $this->updateHostTotals($postData['bytestotal'], $postData['filestotal'], $postData['tracker']);
                    break;
                case 2 :
                    $this->updateHostProgress($postData['bytesscanned'], $postData['filesscanned'], $postData['tracker']);
                    break;
                case 3 :
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
        $updateProgress = $this->getPdo()->prepare('UPDATE hosts SET bytesscanned =?, filesscanned =?, status =4 WHERE tracker =?');
        $updateProgress->execute(array($bytesS, $filesS, $tracker));

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
        $updateName = $this->getPdo()->prepare('UPDATE hosts SET host_name =?, status =2 WHERE tracker =?');
        $updateName->execute(array($hostname, $tracker));

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