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

            file_put_contents('/tmp/results.log', print_r($resultsFileContent, true), FILE_APPEND);

        }

        if (array_key_exists('log', $fileData))
        {
            $logFileContent = file_get_contents($fileData['log']['tmp_name']);
            file_put_contents('/tmp/log.log', print_r($logFileContent, true), FILE_APPEND);

        }

        file_put_contents('/tmp/post.log', print_r($postData, true), FILE_APPEND);




        if (array_key_exists('status', $postData))
        {
            $status = $postData['status'];
            file_put_contents('/tmp/post.log', $status, FILE_APPEND);
            switch($status)
            {
                case 0 :
                    $this->updateHostName($postData['hostname'], $postData['tracker']);
                    break;
                case 1 :
                    $this->updateHostTotals($postData);
                    break;
                case 2 :
                    $this->updateHostProgress($postData);
                    break;
            }
        }




    }

    private function updateHostProgress($postData)
    {
        $updateProgress = $this->getPdo()->prepare('UPDATE hosts SET bytesscanned =? AND filesscanned =? WHERE tracker =?');
        $updateProgress->execute(array($postData['bytesscanned'], $postData['filesscanned'], $postData['tracker']));
    }

    private function updateHostTotals($postData)
    {
        $updateTotals = $this->getPdo()->prepare('UPDATE hosts SET bytestotal =? AND filestotal =? WHERE tracker =?');
        $updateTotals->execute(array($postData['bytestotal'], $postData['filestotal'], $postData['tracker']));
    }

    private function updateHostName($hostname, $tracker)
    {
        file_put_contents('/tmp/post.log', 'HOSTNAME-'.$hostname.PHP_EOL, FILE_APPEND);
        $updateName = $this->getPdo()->prepare('UPDATE hosts SET host_name =? AND status =3 WHERE tracker =?');
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