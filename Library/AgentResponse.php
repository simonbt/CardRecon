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
                $job = array(
                    'result'    =>  explode("\t", $result),
                    'tracker'   =>  $postData['tracker']
                );
                $this->getQueue()->useTube('agent')->put(json_encode($job));
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
         * 5 - Add result
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
                    $this->getQueue()->useTube('agent')->put(json_encode($job));
                    break;
                case 1 :
                    $job = array(
                        "action"        =>  '2',
                        "bytestotal"    =>  $postData['bytestotal'],
                        "filestotal"    =>  $postData['filestotal'],
                        "status"        =>  '3',
                        "tracker"       =>  $postData['tracker']
                    );
                    $this->getQueue()->useTube('agent')->put(json_encode($job));
                    break;
                case 2 :
                    $job = array(
                        "action"        =>  '3',
                        "bytesscanned"  =>  $postData['bytesscanned'],
                        "filesscanned"  =>  $postData['filesscanned'],
                        "tracker"       =>  $postData['tracker']
                    );
                    $this->getQueue()->useTube('agent')->put(json_encode($job));
                    break;
                case 3 :
                    $job = array(
                        "action"        =>  '4',
                        "bytesscanned"  =>  $postData['bytesscanned'],
                        "filesscanned"  =>  $postData['filesscanned'],
                        "tracker"       =>  $postData['tracker'],
                        "profile"       =>  $postData['profile']
                    );
                    $this->getQueue()->useTube('agent')->put(json_encode($job));
                    break;
            }
        }
    }
} 