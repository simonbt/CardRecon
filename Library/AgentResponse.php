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
        }

        if (array_key_exists('log', $fileData))
        {
            $logFileContent = file_get_contents($fileData['log']['tmp_name']);
            file_put_contents('/tmp/log.log', print_r($logFileContent, true), FILE_APPEND);

        }

        if (array_key_exists('status', $postData))
        {
            if ($postData['status'] == '0')
            {
                $updateName = $this->getPdo()->prepare('UPDATE hosts SET host_name =? WHERE tracker =?');
                $updateName->execute(array($postData['hostname'], $postData['tracker']));
            }

        }
        file_put_contents('/tmp/post.log', print_r($postData, true), FILE_APPEND);

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