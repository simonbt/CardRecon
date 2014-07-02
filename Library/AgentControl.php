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

    function __construct($profileID, $pdo) {
        parent::__construct($pdo);
        $this->setProfileInfo($profileID);
        $this->setConfig();
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

        $this->scanName = 'Scan Name';
        file_put_contents('/tmp/config.ini', str_ireplace("\x0D", "", $this->configIni));

        $smb = new Samba('//'.$hostIP.'/C$', $this->profile['username'], $this->profile['password']);

        $createDir = $this->createInstallDir($hostIP);
        print_r($createDir);

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

    public  function startService($hostIP)
    {
        $command = $this->profile['path'] . '/sc.exe start OpenDLP';
        $success = $this->winControl($hostIP, $command);
        return $success;
    }

    public  function stopService($hostIP)
    {
        $command = $this->profile['path'] . '/sc.exe stop OpenDLP';
        $success = $this->winControl($hostIP, $command);
        return $success;
    }

    private function winControl($hostIP, $command)
    {
        $command = 'winexe -U ' . $this->profile['domain'] . '/' . $this->profile['username'] . '%' . $this->profile['password'] . ' //' . $hostIP . ' \'' . $command . '\'';
        echo $command . PHP_EOL;
        exec ( $command, $output, $returnValue);
        return array('output' => $output, 'exitcode' => $returnValue);
    }

    private function unpackService($hostIP)
    {
        $command = $this->profile['path'] . '/OpenDLPz.exe x -y -o"c:/Program Files/OpenDLP/"';
        $success = $this->winControl($hostIP, $command);
        return $success;
    }

    private function createService($hostIP)
    {
        $command = $this->profile['path'] . '/sc.exe create OpenDLP binpath= "c:\\Program Files\\OpenDLP\\OpenDLP.exe" start= auto';

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

    private function setConfig()
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