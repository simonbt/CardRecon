<?php
/**
 * StormRecon - Worker.php.
 * User: simonbeattie
 * Date: 02/07/2014
 * Time: 22:05
 */

namespace Library;


class Worker extends ReconAbstract {

    public function checkSuccess($result, \Pheanstalk_Pheanstalk $job)
    {
        if ($result)
        {
            $this->getLogger()->info('Job completed successfully!');
            $this->getQueue()->delete($job);
        }
        else
        {
            $this->getLogger()->warning('Job FAILED! - Returning job into to the queue', $result);
            $this->getQueue()->bury($job);
        }
    }

    private function uninstallAgent($profileID, $pdo, $hostID)
    {
        $scanner = new \Library\ExistingAgent($profileID[0], $pdo, $hostID[0], $this->getQueue(), $this->getLogger());
        $scanner->killAgent();
    }

    public function hostCompleted($bytesS, $filesS, $tracker, $profile)
    {
        $result = $updateProgress = $this->getPdo()->prepare('UPDATE hosts SET bytesscanned =?, filesscanned =?, end_time =?, status =4 WHERE tracker =?');
        if (!$result)
        {
            return false;
        }
        $updateProgress->execute(array($bytesS, $filesS, date('Y-m-d H:i:s') , $tracker));

        $getHostID = $this->getPdo()->prepare('SELECT id FROM hosts WHERE tracker =?');
        $result = $getHostID->execute(array($tracker));
        if (!$result)
        {
            return false;
        }
        $hostID = $getHostID->fetchAll(\PDO::FETCH_COLUMN);

        $getProfileID = $this->getPdo()->prepare('SELECT id FROM profiles WHERE profile_name =?');
        $result = $getProfileID->execute(array($profile));
        if (!$result)
        {
            return false;
        }
        $profileID = $getProfileID->fetchAll(\PDO::FETCH_COLUMN);

        $this->uninstallAgent($profileID, $this->getPdo(), $hostID, $this->getQueue(), $this->getLogger());
        return true;
    }

    public function updateHostProgress($bytesS, $filesS, $tracker)
    {
        $updateProgress = $this->getPdo()->prepare('UPDATE hosts SET bytesscanned =?, filesscanned =? WHERE tracker =?');
        $result = $updateProgress->execute(array($bytesS, $filesS, $tracker));
        if (!$result)
        {
            return false;
        }
        return true;
    }

    public function updateHostTotals($bytesT, $filesT, $tracker)
    {
        $updateTotals = $this->getPdo()->prepare('UPDATE hosts SET bytestotal =?, filestotal =?, status =3 WHERE tracker =?');
        $result = $updateTotals->execute(array($bytesT, $filesT, $tracker));
        if (!$result)
        {
            return false;
        }
        return true;
    }

    public function updateHostName($hostname, $tracker)
    {

        $updateName = $this->getPdo()->prepare('UPDATE hosts SET host_name =?, start_time =?, status =2 WHERE tracker =?');
        $result = $updateName->execute(array($hostname, date('Y-m-d H:i:s'), $tracker));
        if (!$result)
        {
            return false;
        }
        return true;
    }

    public function addResult($result, $tracker)
    {
        $resultsQuery = $this->getPdo()->prepare('INSERT INTO results (tracker, filename, regex_name, result, offset, md5, zipfile) VALUES (?, ?, ?, ?, ?, ?, ?)');
        if (count($result) > 1)
        {
            array_unshift($result, $tracker);
            $result = $resultsQuery->execute(array_pad($result, 7, null));
            if (!$result)
            {
                return false;
            }
        }
        return true;
    }

}