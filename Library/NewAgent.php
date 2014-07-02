<?php
/**
 * StormRecon - NewAgent.php.
 * User: simonbeattie
 * Date: 02/07/2014
 * Time: 13:35
 */

namespace Library;


class NewAgent extends AgentControl {

    function __construct( $pdo, $profileID, $scanName, $ip_address ) {

        parent::__construct($pdo);
        $this->setScanName($scanName);
        $this->setIP($ip_address);
        $this->setProfileInfo($profileID);
        $this->setConfig();

        $this->createHost();
    }

} 