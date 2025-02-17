<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class ManagerAdvertising extends Manager {

    protected $connection_info;
    protected $database_connection;
    protected $messageHandler;
    protected $redirectURL = '/login.html';
    protected $privilegeChecks = array();
    protected $memberPrivileges = array();


    protected function initialize_ManagerAdvertising() {
        DEFINE('ADVERTISING_AD_POSITIONS_CLASS_NAME', 'ad_positions');
        DEFINE('ADVERTISING_AD_RUNS_CLASS_NAME', 'ad_runs');
        DEFINE('ADVERTISING_ADS_CLASS_NAME', 'ads');

        

    }

    public function getAdPositions () {
        $obj_ad_positions = $this->loadClass(ADVERTISING_AD_POSITIONS_CLASS_NAME);

        return $obj_ad_positions->getList();
        
    }

}

?>
