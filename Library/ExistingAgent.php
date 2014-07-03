<?php
/**
 * StormRecon - ExistingAgent.php.
 * User: simonbeattie
 * Date: 02/07/2014
 * Time: 13:35
 */

namespace Library;


class ExistingAgent extends AgentControl{

    function __construct($profileID, $pdo, $hostID, $queue, $logger)
    {
        parent::__construct($pdo, $queue, $logger);
        $this->setIP($this->getIP($hostID));
        $this->setProfileInfo($profileID);
        $this->setConfig();

    }

    private function getIP($hostID)
    {
        $hosts = new Hosts($this->getPdo(), $this->getQueue(), $this->getLogger());
        $hostDetails = $hosts->hostDetails($hostID);

        return $hostDetails[0]['ip_address'];

    }


} 