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
            __DIR__ . '/../Agent/sc.exe'
        );

        $smb = new Samba('//'.$hostIP.'/C$', $this->profile['username'], $this->profile['password']);

        $this->createInstallDir($hostIP);
        die();

        $smb->mput($agentFiles, $this->profile['path']);

        $this->unpackService($hostIP);
        $this->createService($hostIP);
        $this->startService($hostIP);

    }

    public function killAgent($hostIP)
    {
      $this->stopService($hostIP);
      $this->deleteService($hostIP);
      $this->deleteInstallDir($hostIP);
    }

    private function winControl($hostIP, $command)
    {
        $command = 'winexe -U ' . $this->profile['domain'] . '/' . $this->profile['username'] . '/%' . $this->profile['password'] . ' //' . $hostIP . ' "' . $this->profile['path'] . '/' . $command;

     echo $command;die();
        exec ( $command, $output, $returnValue);
        return $output;
    }

    private function unpackService($hostIP)
    {
        $command = 'StormReconz.exe';
        $success = $this->winControl($hostIP, $command);
        return $success;
    }

    private function createService($hostIP)
    {
        $command = 'sc.exe create OpenDLP binpath= "' . $this->profile['path'] . '/OpenDLP.exe start= auto';
        $success = $this->winControl($hostIP, $command);
        return $success;
    }

    private function startService($hostIP)
    {
        $command = 'sc.exe start OpenDLP';
        $success = $this->winControl($hostIP, $command);
        return $success;
    }

    private function stopService($hostIP)
    {
        $command = 'sc.exe stop OpenDLP';
        $success = $this->winControl($hostIP, $command);
        return $success;
    }

    private function deleteService($hostIP)
    {
        $command = 'sc.exe delete OpenDLP';
        $success = $this->winControl($hostIP, $command);
        return $success;
    }

    private function createInstallDir($hostIP)
    {
        $command = 'cmd.exe /c md "' . $this->profile['path'] . '" /Q /S';
        $success = $this->winControl($hostIP, $command);
        return $success;
    }

    private function deleteInstallDir($hostIP)
    {
        $command = 'cmd.exe /c rd "' . $this->profile['path'] . '" /Q /S';
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