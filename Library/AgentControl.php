<?php
/**
 * StormRecon - AgentControl.php.
 * User: simonbeattie
 * Date: 01/07/2014
 * Time: 10:05
 */

namespace Library;


class AgentControl extends ReconAbstract{

    protected $profile = array();

    function __construct($profileID, $pdo) {
        parent::__construct($pdo);
        $this->setProfileInfo($profileID);
    }

    public function deployAgent($hostIP)
    {
        $agentFiles = array(
            __DIR__ . '/../Agent/OpenDLPz.exe',
            __DIR__ . '/../Agent/config.ini',
            __DIR__ . '/../Agent/sc.exe',
            __DIR__ . '/../Agent/server.pem',
            __DIR__ . '/../Agent/client.pem'
        );

        $smb = new Samba('//'.$hostIP.'/C$', $this->profile['username'], $this->profile['password']);

        $this->createInstallDir($hostIP);
        $transferred = $smb->mput($agentFiles, $this->profile['path']);

        if (!$transferred)
        {
            die('Agent transfer via SMB failed!');
        }

        $unpacked = $this->unpackService($hostIP);
        print_r($unpacked);
        $created = $this->createService($hostIP);
        print_r($created);
        $started = $this->startService($hostIP);
        print_r($started);
    }

    public function killAgent($hostIP)
    {
        $stopped = $this->stopService($hostIP);
        print_r($stopped);
        $deleted = $this->deleteService($hostIP);
        print_r($deleted);
        $deleteDir = $this->deleteInstallDir($hostIP);
        print_r($deleteDir);
    }

    private function winControl($hostIP, $command)
    {
        $command = 'winexe -U ' . $this->profile['domain'] . '/' . $this->profile['username'] . '%' . $this->profile['password'] . ' //' . $hostIP . ' \'' . $command . '\'';
        echo $command . PHP_EOL;
        exec ( $command, $output, $returnValue);
        return $output;
    }

    private function unpackService($hostIP)
    {
        $command = $this->profile['path'] . '/OpenDLPz.exe x -y -o"c:/Program Files/OpenDLP/"';
        $success = $this->winControl($hostIP, $command);
        return $success;
    }

    private function createService($hostIP)
    {
        //$command = $this->profile['path'] . '/sc.exe create OpenDLP binpath= "' . $this->profile['path'] . '/OpenDLP.exe" start= auto';
        $command = $this->profile['path'] . '/sc.exe create OpenDLP binpath= "c:\\Program Files\\OpenDLP\\OpenDLP.exe" start= auto';

        $success = $this->winControl($hostIP, $command);
        return $success;
    }

    private function startService($hostIP)
    {
        $command = $this->profile['path'] . '/sc.exe start OpenDLP';
        $success = $this->winControl($hostIP, $command);
        return $success;
    }

    private function stopService($hostIP)
    {
        $command = $this->profile['path'] . '/sc.exe stop OpenDLP';
        $success = $this->winControl($hostIP, $command);
        return $success;
    }

    private function deleteService($hostIP)
    {
        $command = $this->profile['path'] . '/sc.exe delete OpenDLP';
        $success = $this->winControl($hostIP, $command);
        return $success;
    }

    private function createInstallDir($hostIP)
    {
        $command = 'cmd.exe /c md "' . $this->profile['path'] . '"';
        $success = $this->winControl($hostIP, $command);
        return $success;
    }

    private function deleteInstallDir($hostIP)
    {
        $command = 'cmd.exe /c rd /S /Q "' . $this->profile['path'] . '"';
        $success = $this->winControl($hostIP, $command);
        return $success;

    }

    private function setProfileInfo($profileID)
    {
        $profiles = new Profiles($this->getPdo());
        $profileDetails = $profiles->profileDetails($profileID);
        $this->profile = array(
            'username'  =>  $profileDetails[0]['username'],
            'password'  =>  $profileDetails[0]['password'],
            'domain'    =>  $profileDetails[0]['domain'],
            'path'      =>  $profileDetails[0]['path']
        );
    }
}