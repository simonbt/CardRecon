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

        $this->scanName = 'Scan Name';
        file_put_contents('/tmp/config.log', $this->createConfig());

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

    private function ignores($extensions)
    {
        $output = explode("\n", $extensions);
        $return = "";
        foreach ($output as $line)
        {
            $return .="ext=" . $line . "\n";
        }

        return $return;
    }

    private function directories($directories)
    {

    }

    private function createConfig()
    {
        $config = <<<CONFIG
# Scan name
scan={$this->scanName}

# Profile name
profile={$this->profile['profile']}

# Ignore certain extensions? Valid options are:
#   everything - scan all files regardless of extension
#   ignore - ignore files ending in the following extensions
#   allow - only look at files ending in the following extensions
ext_opt={$this->profile['ignore_exts']}
{$this->ignores($this->profile['exts'])}}

# Ignore certain directories? Valid options are:
#   everything - scan all directories
#   ignore - ignore the following directories
#   allow - only scan the following directories
dir_opt={$this->profile['ignore_dirs']}
{$this->ignores($this->profile['dirs'])}

# Use the following regular expressions
regex=Custom_Test:boot
regex=Credit_Card_Track_1:(\D|^)\%?[Bb]\d{13,19}\^[\-\/\.\w\s]{2,26}\^[0-9][0-9][01][0-9][0-9]{3}
regex=Credit_Card_Track_2:(\D|^)\;\d{13,19}\=(\d{3}|)(\d{4}|\=)
regex=Credit_Card_Track_Data:[1-9][0-9]{2}\-[0-9]{2}\-[0-9]{4}\^\d
regex=Mastercard:(\D|^)5[1-5][0-9]{2}(\ |\-|)[0-9]{4}(\ |\-|)[0-9]{4}(\ |\-|)[0-9]{4}(\D|$)
regex=Visa:(\D|^)4[0-9]{3}(\ |\-|)[0-9]{4}(\ |\-|)[0-9]{4}(\ |\-|)[0-9]{4}(\D|$)
regex=AMEX:(\D|^)(34|37)[0-9]{2}(\ |\-|)[0-9]{6}(\ |\-|)[0-9]{5}(\D|$)
regex=Diners_Club_1:(\D|^)30[0-5][0-9](\ |\-|)[0-9]{6}(\ |\-|)[0-9]{4}(\D|$)
regex=Diners_Club_2:(\D|^)(36|38)[0-9]{2}(\ |\-|)[0-9]{6}(\ |\-|)[0-9]{4}(\D|$)
regex=Discover:(\D|^)6011(\ |\-|)[0-9]{4}(\ |\-|)[0-9]{4}(\ |\-|)[0-9]{4}(\D|$)
regex=JCB_1:(\D|^)3[0-9]{3}(\ |\-|)[0-9]{4}(\ |\-|)[0-9]{4}(\ |\-|)[0-9]{4}(\D|$)
regex=JCB_2:(\D|^)(2131|1800)[0-9]{11}(\D|$)

# This is used so the OpenDLP agent knows which regexes are credit card numbers.
# Knowing this, the OpenDLP agent will perform further checks on these potential matches
# to determine if they are valid credit card numbers.
creditcard=Mastercard
creditcard=Visa
creditcard=AMEX
creditcard=Diners_Club_1
creditcard=Diners_Club_2
creditcard=Discover
creditcard=JCB_1
creditcard=JCB_2

# These file extensions tell OpenDLP to process the files as ZIPs.
zipfile=zip
zipfile=jar
zipfile=xlsx
zipfile=docx
zipfile=pptx
zipfile=odt
zipfile=odp
zipfile=ods
zipfile=odg

# This is the duration to wait before uploading new results to the web server.
wait=10

# This is the location where to upload scan data.
uploadurl=http://192.168.200.22/agent

# This is the username for the upload URL.
urluser=ddt

# This is the password for the upload URL.
urlpass=rand0m

# This is the setting that controls the verbosity of logs.
debug=3

# This is the maximum percent of available memory to use for processing files.
# If a file is greater than this, it will be split into chunks.
memory=0.1

# Random string used for host tracking purposes.
tracker=C10A868EBD0304CEDBC75B86E16FBA52

CONFIG;

        return $config;

    }
}