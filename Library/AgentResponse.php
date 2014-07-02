<?php
/**
 * StormRecon - AgentResponse.php.
 * User: simonbeattie
 * Date: 02/07/2014
 * Time: 09:05
 */

namespace Library;

class AgentResponse {

    public function receive($postData)
    {
        if (array_key_exists('results', $postData))
        {
            $resultsFileContent = file_get_contents($postData['results']['tmp_name']);
            $resultsLine = explode("\n", $resultsFileContent);
            foreach ($resultsLine as $result)
            {
                file_put_contents(__DIR__ . '/results.log', print_r(explode("\t", $result), true), FILE_APPEND);
            }
        }

        $logFileContent = file_get_contents($postData['log']['tmp_name']);

        file_put_contents(__DIR__ . '/log.log', print_r($logFileContent, true), FILE_APPEND);
        file_put_contents(__DIR__ . '/post.log', print_r($postData, true), FILE_APPEND);

    }
} 