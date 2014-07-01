<?php
/**
 * StormRecon - Hosts.php.
 * User: simonbeattie
 * Date: 01/07/2014
 * Time: 07:42
 */

namespace Library;


class Hosts extends ReconAbstract{

    public function listHosts()
    {
        $hostsQuery = $this->getPdo()->prepare('SELECT id, host_name, ip_address FROM hosts');
        $hostsQuery->execute();
        $hostList = $hostsQuery->fetchAll(\PDO::FETCH_ASSOC);

        return $hostList;
    }

    public function hostDetails($id)
    {
        $hostsQuery = $this->getPdo()->prepare('SELECT * from hosts where id =?');
        $hostsQuery->execute(array($id));
        $hostDetails = $hostsQuery->fetchAll(\PDO::FETCH_ASSOC);

        return $hostDetails;
    }


    public function addHost($postData)
    {
        $hostFields = array('host_name', 'ip_address', 'type');

        foreach ($hostFields as $key)
        {
            if (!array_key_exists($key, $postData))
            {
                die('You must post a ' . $key);
            }
        }

        $hostsQuery = $this->getPdo()->prepare('INSERT INTO hosts (host_name, ip_address, type) VALUES(? ,? ,?)');
        $response = $hostsQuery->execute(array($postData['host_name'], $postData['ip_address'], $postData['type']));

        if (!$response)
        {
            die('Failed to add new host -- ' . print_r($hostsQuery->errorInfo()));
        }

        return true;
    }

    public function deleteHost($id)
    {

        $hostsQuery = $this->getPdo()->prepare('DELETE FROM hosts WHERE id =?');
        $response = $hostsQuery->execute(array($id));

        if (!$response)
        {
            die('Failed to delete host id: ' . $id . ' -- ' . print_r($hostsQuery->errorInfo()));
        }

        return true;
    }

    public function updateHost($id, $putData)
    {
        $hostFields = array('host_name', 'ip_address', 'type');

        foreach ($hostFields as $key)
        {
            if (array_key_exists($key, $putData))
            {
                $hostsQuery = $this->getPdo()->prepare('UPDATE hosts SET ' . $key . ' =? WHERE id =?');
                $response = $hostsQuery->execute(array($putData[$key], $id));

                if (!$response)
                {
                    die('Failed to update host value: ' . $key . ' -- ' . print_r($hostsQuery->errorInfo()));
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