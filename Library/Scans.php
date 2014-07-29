<?php
/**
 * StormRecon - Scans.php.
 * User: simonbeattie
 * Date: 30/06/2014
 * Time: 16:37
 */

namespace Library;

class Scans extends ReconAbstract{

    public function listScans()
    {
        $scansQuery = $this->getPdo()->prepare('SELECT * FROM scan');
        $scansQuery->execute();
        $scans = $scansQuery->fetchAll(\PDO::FETCH_ASSOC);

        return $scans;
    }

    public function listCurrentHosts()
    {
        $query = $this->getPdo()->prepare('SELECT * FROM hosts WHERE NOT status =4');
        $query->execute();
        $current = $query->fetchAll(\PDO::FETCH_ASSOC);

        return $current;
    }

    public function deleteScan($id)
    {

        $scanQuery = $this->getPdo()->prepare('DELETE FROM scan WHERE id =?');
        $response = $scanQuery->execute(array($id));

        if (!$response)
        {
            die('Failed to delete scan id: ' . $id . ' -- ' . print_r($scanQuery->errorInfo()));
        }

        return true;
    }

    public function addScan($postData)
    {
        $queue =  new \Pheanstalk_Pheanstalk('127.0.0.1:11300');
        $scan = array('scan_name', 'profile_id', 'status', 'hosts');

        foreach ($scan as $key)
        {
            if (!array_key_exists($key, $postData))
            {
                die('You must post a ' . $key);
            }
        }

        $scanQuery = $this->getPdo()->prepare('INSERT INTO scan (scan_name, added, profile_id, status, hosts) VALUES(?, ?, ?, ?, ?)');
        $response = $scanQuery->execute(array($postData['scan_name'], date('Y-m-d H:i:s'), $postData['profile_id'], $postData['status'], $postData['hosts']));
        if (!$response)
        {
            die('Failed to insert new scan -- ' . print_r($scanQuery->errorInfo()));
        }
        foreach (explode(",",$postData['hosts']) as $ip_address)
        {
            $job = array(
                'action'            =>  '1',
                'scan_name'         =>  $postData['scan_name'],
                'profile_id'        =>  $postData['profile_id'],
                'ip_address'        =>  $ip_address
            );
            $queue->useTube('deployment')->put(json_encode($job));
        }
        return true;
    }

    public function updateScan($id, $putData)
    {
        $scan = array('name', 'pattern');
        foreach ($scan as $key)
        {
            if (array_key_exists($key, $putData))
            {
                $scanQuery = $this->getPdo()->prepare('UPDATE scan SET ' . $key . ' =? WHERE id =?');
                $response = $scanQuery->execute(array($putData[$key], $id));

                if (!$response)
                {
                    die('Failed to update scan value: ' . $key . ' -- ' . print_r($scanQuery->errorInfo()));
                }
            }
            else
            {
                die('You haven\'t posted any data!');
            }
        }

        return true;
    }
} 