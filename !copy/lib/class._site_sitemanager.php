<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author nuForge
 *
 * FOR LAUNCH TODO LIST
 * TODO PRIVACY SETTING FOR Message and Session Booking - Verified Members, etc.
 *
 *
 * COMPLETED
 * DONE Easier Wrestler Application
 * DONE Generate Unique Wrestler Name
 * DONE Membership Limits For Photos, Links
 * DONE Send Notification Emails On:
 *      Message - DONE
 *      Session Request - DONE
 *      Session Communication - DONE
 *      Session Updates (approved, rejected, cancelled, new comment) - DONE
 *      Promoted -
 * DONE Send notification emails: Check Message Settings
 * DONE Allow Wrestlers to:
 *      Approve Session - DONE
 *      Reject Session - DONE
 *      Complete Session - DONE
 *      Cancel Session - DONE
 *
 *      Delete Session - UNECESSARY
 * DONE Blacklist Comments Interface - DONE
 * DONE Add FEATURED Wrestler Interface
 * DONE Allow Members to Edit/Cancel their Session Request
 *
 *
 *
 * POST LAUNCH TODO LIST
 * TODO Non-recurring Premium Membership Subscription
 * TODO PREMIUM MEMBERSHIP SUBSCRIPTION MANAGER
 * TODO Send Notification Emails On:
 *      Premium Subscription Ending
 *      Featured Wrestler Ending
 * TODO Allow Texting via email
 * TODO Ad Runs and Ad Positions
 * TODO Advertising Interface
 * TODO Events
 * TODO Business Directory
 * TODO Arenas and Rental Spaces
 * 
 */
class _site_SiteManager extends ManagerSiteManager
{

    protected $connection_info = array('user' => '', 'password' => '', 'dbname' => '');
    protected $database_connection;
    protected $messageHandler;
    protected $PrivateMessageManager;
    protected $googleCaptchaCode;
    protected $redirectURL = '/login.html';
    protected $privilegeChecks = array();
    protected $memberPrivileges = array();
    protected $page;

    protected function initialize__site_SiteManager()
    {

        ini_set("display_errors", "1");


        DEFINE('SITE_MAINURL', 'http://www._site.com/');

        $this->loadConfiguration();
        return true;
    }

    public function loadConfiguration()
    {
        $obj_configurations = $this->loadClass('configurations');

        $this->confirgurationData = $obj_configurations->load(1);
        return $this->confirgurationData;
    }

    public function getConfiguration()
    {
        return $this->confirgurationData;
    }
}
