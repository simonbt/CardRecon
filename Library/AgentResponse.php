<?php
/**
 * StormRecon - AgentResponse.php.
 * User: simonbeattie
 * Date: 02/07/2014
 * Time: 09:05
 */

namespace Library;

class AgentResponse {

    public function receive($postData, $fileData)
    {
        if (array_key_exists('results', $fileData))
        {
            $resultsFileContent = file_get_contents($fileData['results']['tmp_name']);
            $resultsLine = explode("\n", $resultsFileContent);
            foreach ($resultsLine as $result)
            {
                file_put_contents('/tmp/results.log', print_r(explode("\t", $result), true), FILE_APPEND);
            }
        }

        if (array_key_exists('log', $fileData))
        {
            $logFileContent = file_get_contents($fileData['log']['tmp_name']);
            file_put_contents('/tmp/log.log', print_r($logFileContent, true), FILE_APPEND);

        }

        file_put_contents('/tmp/post.log', print_r($postData, true), FILE_APPEND);


    }
} 