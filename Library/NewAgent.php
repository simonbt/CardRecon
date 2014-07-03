<?php
/**
 * StormRecon - NewAgent.php.
 * User: simonbeattie
 * Date: 02/07/2014
 * Time: 13:35
 */

namespace Library;


class NewAgent extends AgentControl {

    function __construct( $pdo, $profileID, $scanName, $ip_address ,$queue, $logger) {

        parent::__construct($pdo, $queue, $logger);
        $this->setScanName($scanName);
        $this->setIP($ip_address);
        $this->setProfileInfo($profileID);
        $this->createHost();

        $this->setConfig();
    }

} 