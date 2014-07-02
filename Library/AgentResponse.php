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
                    $this->updateHostTotals($postData['bytestotal'], $postData['filestotal'], $postData['tracker']);
                    break;
                case 2 :
                    $this->updateHostProgress($postData['bytesscanned'], $postData['filesscanned'], $postData['tracker']);
                    break;
            }
        }




    }

    private function updateHostProgress($bytesS, $filesS, $tracker)
    {
        $updateProgress = $this->getPdo()->prepare('UPDATE hosts SET bytesscanned =?, filesscanned =? WHERE tracker =?');
        $updateProgress->execute(array($bytesS, $filesS, $tracker));
    }

    private function updateHostTotals($bytesT, $filesT, $tracker)
    {
        $updateTotals = $this->getPdo()->prepare('UPDATE hosts SET bytestotal =?, filestotal =? WHERE tracker =?');
        $updateTotals->execute(array($bytesT, $filesT, $tracker));
        file_put_contents('/tmp/error.log', print_r($updateTotals->errorInfo(), true), FILE_APPEND);

    }

    private function updateHostName($hostname, $tracker)
    {
        $updateName = $this->getPdo()->prepare('UPDATE hosts SET host_name =?, status =3 WHERE tracker =?');
        $updateName->execute(array($hostname, $tracker));
        file_put_contents('/tmp/error.log', print_r($updateName->errorInfo(), true), FILE_APPEND);

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