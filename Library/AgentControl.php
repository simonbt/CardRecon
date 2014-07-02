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
    protected $scanName;
    protected $tracker;
    protected $configIni;
    protected $ip_address;

    public function setIP($ip_address)
    {
        $this->ip_address = $ip_address;
    }
    public function setScanName($scanName)
    {
        $this->scanName = $scanName;
    }

    public function deployAgent()
    {
        $agentFiles = array(
            __DIR__ . '/../Agent/OpenDLPz.exe',
            __DIR__ . '/../Agent/sc.exe',
            __DIR__ . '/../Agent/server.pem',
            __DIR__ . '/../Agent/client.pem'
        );

        $smb = new Samba('//'.$this->ip_address.'/C$', $this->profile['username'], $this->profile['password']);

        $createDir = $this->createInstallDir($this->ip_address);
        if(!$createDir['exitcode'] == '0')
        {
            die('Failed to create directory - Exit Code: ' . $createDir['exitcode']);
        }

        $transferred = $smb->mput($agentFiles, $this->profile['path']);
        if (!$transferred)
        {
            $this->deleteInstallDir();
            die('Agent transfer via SMB failed!  ' . print_r($smb->get_last_cmd_stdout()));
        } else { echo 'success' . PHP_EOL; }

        file_put_contents('/tmp/'.$this->tracker.'.ini', str_ireplace("\x0D", "", $this->configIni));
        $transferredConfig = $smb->configPut('/tmp/'.$this->tracker.'.ini', $this->profile['path'].'/config.ini');
        if (!$transferredConfig)
        {
            $this->deleteInstallDir();
            die('Config transfer via SMB failed!  ' . print_r($smb->get_last_cmd_stdout()) . PHP_EOL);
        } else { echo 'success' . PHP_EOL; }

        $unpacked = $this->unpackService();
        if(!$unpacked['exitcode'] == '0')
        {
            $this->deleteInstallDir();
            die('Failed to unpack agent - Exit Code: ' . $unpacked['exitcode'] . PHP_EOL);
        } else { echo 'success' . PHP_EOL; }

        $created = $this->createService();
        if(!$created['exitcode'] == '0')
        {
            $this->deleteInstallDir();
            die('Failed to unpack agent - Exit Code: ' . $created['exitcode'] . PHP_EOL);
        } else { echo 'success' . PHP_EOL; }


        $started = $this->startService();
        if(!$started['exitcode'] == '0')
        {
            $this->deleteService();
            $this->deleteInstallDir();
            die('Failed to unpack agent - Exit Code: ' . $started['exitcode'] . PHP_EOL);
        } else { echo 'success' . PHP_EOL; }
    }


    public function killAgent()
    {
        $stopped = $this->stopService();
        if(!$stopped['exitcode'] == '0')
        {
            die('Failed to unpack agent - Exit Code: ' . $stopped['exitcode'] . PHP_EOL);
        } else { echo 'success' . PHP_EOL; }

        $deleted = $this->deleteService();
        if(!$deleted['exitcode'] == '0')
        {
            die('Failed to unpack agent - Exit Code: ' . $stopped['exitcode'] . PHP_EOL);
        } else { echo 'success' . PHP_EOL; }

        $deleteDir = $this->deleteInstallDir();
        if(!$deleteDir['exitcode'] == '0')
        {
            die('Failed to unpack agent - Exit Code: ' . $stopped['exitcode'] . PHP_EOL);
        } else { echo 'success' . PHP_EOL; }
    }

    public  function startService()
    {
        $command = $this->profile['path'] . '/sc.exe start OpenDLP';
        $success = $this->winControl($command);
        return $success;
    }

    public  function stopService()
    {
        $command = $this->profile['path'] . '/sc.exe stop OpenDLP';
        $success = $this->winControl($command);
        return $success;
    }

    public function createHost()
    {
        $hosts = new Hosts($this->getPdo());
        $hostToAdd = array(
            'host_name'     =>  null,
            'ip_address'    =>  $this->ip_address,
            'type'          =>  '1'
        );
        $this->tracker = $hosts->addHost($hostToAdd);
    }

    private function winControl($command)
    {
        $command = 'winexe -U ' . $this->profile['domain'] . '/' . $this->profile['username'] . '%' . $this->profile['password'] . ' //' . $this->ip_address . ' \'' . $command . '\'';
        echo $command . PHP_EOL;
        exec ( $command, $output, $returnValue);
        return array('output' => $output, 'exitcode' => $returnValue);
    }

    private function unpackService()
    {
        $command = $this->profile['path'] . '/OpenDLPz.exe x -y -o"c:/Program Files/OpenDLP/"';
        $success = $this->winControl($command);
        return $success;
    }

    private function createService()
    {
        $command = $this->profile['path'] . '/sc.exe create OpenDLP binpath= "c:\\Program Files\\OpenDLP\\OpenDLP.exe" start= auto';

        $success = $this->winControl($command);
        return $success;
    }

    private function deleteService()
    {
        $command = $this->profile['path'] . '/sc.exe delete OpenDLP';
        $success = $this->winControl($command);
        return $success;
    }

    private function createInstallDir()
    {
        $command = 'cmd.exe /c md "' . $this->profile['path'] . '"';
        $success = $this->winControl($command);
        return $success;
    }

    private function deleteInstallDir()
    {
        $command = 'cmd.exe /c rd /S /Q "' . $this->profile['path'] . '"';
        $success = $this->winControl($command);
        return $success;

    }

    public function setProfileInfo($profileID)
    {
        $profiles = new Profiles($this->getPdo());
        $profileDetails = $profiles->profileDetails($profileID);
        $this->profile = $profileDetails[0];
    }

    private function lists($prefix, $extensions)
    {
        $output = explode("\n", $extensions);
        $return = "";
        foreach ($output as $line)
        {
            $return .= $prefix . "=" . $line . PHP_EOL;
        }

        return $return;
    }

    private function listRegex()
    {
        $regex = new Regex($this->getPdo());
        $regexList = $regex->listRegex();
        $return = "";

        foreach (explode(",", $this->profile['regex']) as $regexItem)
        {
            foreach ($regexList as $item)
            {
                if ($item['id'] == $regexItem)
                {
                    $return .="regex=" . $item['name'] . ":" . $item['pattern'] . PHP_EOL;
                }
            }
        }
        return $return;
    }

    public function setConfig()
    {

        $this->configIni = <<<CONFIG
# Scan name
scan={$this->scanName}

# Profile name
profile={$this->profile['profile_name']}

# Ignore certain extensions? Valid options are:
#   everything - scan all files regardless of extension
#   ignore - ignore files ending in the following extensions
#   allow - only look at files ending in the following extensions
ext_opt={$this->profile['ignore_exts']}
{$this->lists('ext', $this->profile['exts'])}

# Ignore certain directories? Valid options are:
#   everything - scan all directories
#   ignore - ignore the following directories
#   allow - only scan the following directories
dir_opt={$this->profile['ignore_dirs']}
{$this->lists('dir', $this->profile['dirs'])}

# Use the following regular expressions
{$this->listRegex()}

# This is used so the OpenDLP agent knows which regexes are credit card numbers.
# Knowing this, the OpenDLP agent will perform further checks on these potential matches
# to determine if they are valid credit card numbers.
{$this->lists('creditcard', $this->profile['creditcards'])}

# These file extensions tell OpenDLP to process the files as ZIPs.
{$this->lists('zipfile', $this->profile['zipfiles'])}

# This is the duration to wait before uploading new results to the web server.
wait={$this->profile['update_frequency']}

# This is the location where to upload scan data.
uploadurl={$this->profile['serverurl']}

# This is the username for the upload URL.
urluser={$this->profile['serveruser']}

# This is the password for the upload URL.
urlpass={$this->profile['serverpass']}

# This is the setting that controls the verbosity of logs.
debug={$this->profile['debug_level']}

# This is the maximum percent of available memory to use for processing files.
# If a file is greater than this, it will be split into chunks.
memory={$this->profile['memory']}

# Random string used for host tracking purposes.
tracker={$this->tracker}

CONFIG;

    }
}