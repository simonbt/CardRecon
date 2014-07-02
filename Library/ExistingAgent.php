<?php
/**
 * StormRecon - ExistingAgent.php.
 * User: simonbeattie
 * Date: 02/07/2014
 * Time: 13:35
 */

namespace Library;


class ExistingAgent extends AgentControl{

    function __construct($profileID, $pdo, $hostID)
    {
        parent::__construct($pdo);
        $this->setIP($this->getIP($hostID));
        $this->setProfileInfo($profileID);
        $this->setConfig();

    }

    private function getIP($hostID)
    {
        $hosts = new Hosts($this->getPdo());
        $hostDetails = $hosts->hostDetails($hostID);

        return $hostDetails[0]['ip_address'];

    }


} 