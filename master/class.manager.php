<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Manager {

    protected $myPrimaryMember;
    protected $myCurrentMember;
    protected $connection_info;
    protected $database_connection;
    protected $messageHandler;
    protected $privilegeChecks = array();
    protected $memberPrivileges = array();

    public function __construct() {
        $this->initialize();
    }

    public function __destruct() {
        //$this->initializeTuringCode();
        if (!empty($this->database_connection)) {
            @pg_close($this->database_connection);
        }
    }
    
    protected function initialize_Manager() {
        DEFINE('MANAGER_DEBUG_SESSION_NAME', 'debug_out_ip');

        DEFINE('MANAGER_MEMBERS_CLASS_NAME','members');
        DEFINE('MANAGER_MEMBER_PRIVILEGES_CLASS_NAME','member_privileges');
        DEFINE('MANAGER_PRIVILEGES_CLASS_NAME','privileges');

        DEFINE('PRIVILEGE_CLASS_ALL', 'ALL');
        DEFINE('PRIVILEGE_CLASS_ANY', 'ANY');
        DEFINE('PRIVILEGE_CLASS_MUST', 'MUST');
        DEFINE('PRIVILEGE_CLASS_RESTRICT', 'RESTRICT');

        DEFINE('USERNAME_PATTERN', '<<([a-zA-Z0-9_-]*)>>');
        DEFINE('PASSWORD_PATTERN', '/^[A-Za-z0-9._@!#$+-]+$/');
        DEFINE('EMAIL_PATTERN', "/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/");

        DEFINE('REGEX_PATTERN_URL', "/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i");
        DEFINE('REGEX_PATTERN_USERNAME', '/^([a-zA-Z0-9_-]*)$/');
        DEFINE('REGEX_PATTERN_PASSWORD', '/^[A-Za-z0-9._@!#$+-]+$/');
        DEFINE('REGEX_PATTERN_EMAIL', "/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/");

        DEFINE('MEMBER_PRIVILEGE_ADMIN', 'admin');
        DEFINE('MEMBER_PRIVILEGE_VIP', 'vip');
        DEFINE('MEMBER_PRIVILEGE_CREATOR', 'creator');

        DEFINE('MAX_LARGE_TEXT_COUNT', 10000);
        DEFINE('MAX_MEDIUM_TEXT_COUNT', 1000);
        DEFINE('MAX_SMALL_TEXT_COUNT', 140);

        DEFINE('TURING_POST_NAME', 'human_verification_code');

        DEFINE('METERS_TO_MILES', 0.000621371192);
        DEFINE('METERS_TO_KM', 0.001);
        DEFINE('MILES_TO_METERS', 1609.344);
        DEFINE('KM_TO_METERS', 1000);

        DEFINE('INCHES_TO_CM', 2.54);
        DEFINE('CM_TO_INCHES', 0.393700787402);
        DEFINE('LBS_TO_KG', 0.45359237);
        DEFINE('KG_TO_LBS', 2.20462262);

        $this->messageHandler = new messageHandler('popup');
        $this->connectToDatabase();
        return true;
    }

    protected function initialize() {
        $currentClass = get_class($this);
        while ($currentClass) {
            $method_name = 'initialize_' . $currentClass;
            if (method_exists($this, $method_name)) {
                $class_function[$currentClass] = $method_name;
                call_user_func(array($this,$method_name));
            }
            $currentClass = get_parent_class($currentClass);
        }
        return true;
    }

    public function getDatabaseConnection() {
        return $this->database_connection;
    }

    public function connectToDatabase($fa_connectionInfo = false) {
        $connectionInfo = ($fa_connectionInfo) ? $fa_connectionInfo : $this->connection_info;
        $this->database_connection = (!empty($connectionInfo)) ? DB_Connection::connect($connectionInfo) : false;
    }

    public function setDatabaseConnection($fobj_database_connection) {
        $this->database_connection = $fobj_database_connection;
    }

    public function setPrimaryMember($f_member) {
        $obj_members = $this->loadClass(MANAGER_MEMBERS_CLASS_NAME);
        $member = $obj_members->load($f_member);
        if (empty($member)) {
            return false;
        }
        $this->myPrimaryMember = $member['member'];
        $this->myCurrentMember = $member['member'];
        return $this->myPrimaryMember;
    }

    public function checkCurrentMember($f_member=false) {
        if ($f_member && $f_member != $this->myPrimaryMember) {
            $obj_members = $this->loadClass(PRIVATE_MESSAGES_MANAGER_MEMBERS_CLASS_NAME);
            $member = $obj_members->load($f_member);
            $this->myCurrentMember = $member['member'];
        } else {
            $this->myCurrentMember = $this->myPrimaryMember;
        }
        return $this->myCurrentMember;
    }
    
    public function sendNotificationEmail($f_email_name, $fa_email_values=false) {

        $email = new email();

        $fa_email_values['mainurl'] = $this->pageData['mainurl'];
        $fa_email_values['sitename'] = $this->pageData['sitename'];
        $fa_email_values['phpversion'] = phpversion();
        if ($email->sendEmailTemplate($f_email_name, $fa_email_values)) {
            return true;
        }
        return false;
    }
    
    public function encodeString($f_string, $f_replace='-', $f_lower=true) {
        $f_string = preg_replace("[\-$]", '', preg_replace("[\W+]", $f_replace, $f_string));
        if ($f_lower) {
            return strtolower($f_string);
        }
        return $f_string;
    }

    public function cleanString($f_string, $f_strip_tags=true, $f_trim=true, $f_html_special_characters=true) {
        if (is_array($f_string)) {
            foreach ($f_string as $k => $string) {
                $f_string[$k] = $this->cleanString($string);
            }
        } else {
            $f_string = ($f_strip_tags) ? strip_tags($f_string) : $f_string;
            $f_string = ($f_html_special_characters) ? htmlspecialchars($f_string) : $f_string;
            $f_string = ($f_trim) ? trim($f_string) : $f_string;

        }
        return $f_string;
    }

    public function loadClass($f_class_name) {
        if (empty($this->loadedClasses[$f_class_name])) {
            $this->loadedClasses[$f_class_name] = new $f_class_name($this->database_connection);
            if ($this->loadedClasses[$f_class_name]->getUsedClasses()) {
                foreach ($this->loadedClasses[$f_class_name]->getUsedClasses() as $className) {
                    $this->loadedClasses[$f_class_name]->passClass($this->loadClass($className));
                }
            }
        }
        return $this->loadedClasses[$f_class_name];
    }

    public function loadClasses($fa_class_names) {

        foreach ($fa_class_names as $className) {
            $return[] = loadClass($className);
        }

        return $return;
    }

    public function loadObject($f_object_class, $f_where) {
        $obj_temp = $this->loadClass($f_object_class);
        return $obj_temp->load($f_where);
    }

    public function getObjectList($f_object_class, $f_where, $f_page_number=1, $f_show_per_page=25, $f_order=false) {
        $obj_temp = $this->loadClass($f_object_class);
        $offset = ($f_page_number && $f_show_per_page) ? (($f_page_number - 1) * $f_show_per_page) : false;
        return $obj_temp->getList($f_order, $f_where, $f_show_per_page, $offset);
    }
    public function setStatusMessageOutput($f_message_output_type){
        
        $this->messageHandler->setOutputType($f_message_output_type);
    }

    public function addStatusMessage($f_message, $f_errorType='error') {
        return $this->messageHandler->add($f_errorType, $f_message);
    }

    public function returnStatusMessage($f_errorType=false) {
        return $this->messageHandler->output($f_errorType);
    }

    public function checkStatusMessage($f_errorType='error') {
        return $this->messageHandler->status($f_errorType);
    }

    public function checkHasStatusMessage() {
        return $this->messageHandler->has_message();
    }


    public function confirmPassword($f_member, $f_password) {
        $obj_members = $this->loadClass(MANAGER_MEMBERS_CLASS_NAME);
        $mem = $obj_members->load($f_member);
        //echo md5($f_password). ' == ' . $mem['mem_password'];
        if (md5($f_password) == $mem['mem_password']) {
            return true;
        } else {
            $this->addStatusMessage('Invalid Password');
            return false;
        }
    }

    public function loadMemberPrivileges($f_member_id) {
        $obj_member_privileges = $this->loadClass(MANAGER_MEMBER_PRIVILEGES_CLASS_NAME);
        $privileges = $obj_member_privileges->getSelectList('mp_privilege', 'privilege_unique', false, array('mp_member' => $f_member_id));
        $this->setMemberPrivileges($privileges);
        return $privileges;
    }

    public function getPrivilegeInterfaces($f_member_id) {
        $obj_member_privileges = $this->loadClass(MANAGER_MEMBER_PRIVILEGES_CLASS_NAME);
        return $obj_member_privileges->getList(array('mp_privilege' => 'asc'), array('mp_member' => $f_member_id));
    }

    public function addPrivilegeCheck($f_privilegeId, $f_privilege_class = false) {
        $f_privilege_class = (!$f_privilege_class) ? PRIVILEGE_CLASS_MUST : $f_privilege_class;
        $this->privilegeChecks[$f_privilege_class][] = $f_privilegeId;
        return true;
    }

    public function addMultiplePrivilegeChecks($fa_privilege_ids) {
        foreach ($fa_privilege_ids as $privilege => $class) {
            $this->privilegeChecks[$class][] = $privilege;
        }
        return true;
    }

    public function checkPrivileges($f_member, $f_privilege_class=false, $f_redirect = true) {
        $f_privilege_class = (!$f_privilege_class) ? PRIVILEGE_CLASS_ALL : $f_privilege_class;
        if (!empty($f_member) && empty($this->privilegeChecks)) {
            return true;
        }
        $this->loadMemberPrivileges($f_member);
        switch ($f_privilege_class) {
            case PRIVILEGE_CLASS_ALL:
                $this->passCheck = $this->checkPrivilegesAll();
                break;
            case PRIVILEGE_CLASS_ANY:
                $this->passCheck = $this->checkPrivilegesAny($this->privilegeChecks[PRIVILEGE_CLASS_ANY]);
                break;
            case PRIVILEGE_CLASS_MUST:
                $this->passCheck = $this->checkPrivilegesMust($this->privilegeChecks[PRIVILEGE_CLASS_MUST]);
                break;
            case PRIVILEGE_CLASS_RESTRICT:
                $this->passCheck = $this->checkPrivilegesRestrict($this->privilegeChecks[PRIVILEGE_CLASS_RESTRICT]);
                break;
            default:
                $this->passCheck = $this->checkPrivilegesAll();
                break;
        }
        if (empty($f_member)) {
            $this->passCheck = false;
        }
        if ($this->passCheck) {
            return true;
        } else {
            if ($f_redirect) {
                header('location: ' . $this->redirectURL);
            } else {
                return false;
            }
        }
    }

    public function checkPrivilegesAll() {
        if (!$this->checkPrivilegesAny($this->privilegeChecks[PRIVILEGE_CLASS_ANY])) {
            return false;
        }
        if (!$this->checkPrivilegesMust($this->privilegeChecks[PRIVILEGE_CLASS_MUST])) {
            return false;
        }
        if (!$this->checkPrivilegesRestrict($this->privilegeChecks[PRIVILEGE_CLASS_RESTRICT])) {
            return false;
        }
        return true;
    }

    public function checkPrivilegesAny($fa_privileges_any) {
        if (empty($fa_privileges_any)) {
            return true;
        }
        if (empty($this->memberPrivileges)) {
            return false;
        }
        foreach ($f_privileges_any as $privilege) {
            if (in_array($privilege, $this->memberPrivileges)) {
                return true;
            }
        }
        return false;
    }

    public function checkPrivilegesMust($fa_privileges_must) {
        if (empty($fa_privileges_must)) {
            return true;
        }
        if (empty($this->memberPrivileges)) {
            return false;
        }
        foreach ($fa_privileges_must as $privilege) {
            if (!in_array($privilege, $this->memberPrivileges)) {
                return false;
            }
        }
        return true;
    }

    public function checkPrivilegesRestrict($fa_privileges_restrict) {
        if (empty($fa_privileges_restrict)) {
            return true;
        }
        if (empty($this->memberPrivileges)) {
            return false;
        }
        foreach ($fa_privileges_restrict as $privilege) {
            if (in_array($privilege, $this->memberPrivileges)) {
                return false;
            }
        }
        return true;
    }

    public function checkSinglePrivilege($f_member, $f_privilege, $f_restrict=false, $f_redirect=false) {
        $obj_member_privileges = $this->loadClass(MANAGER_MEMBER_PRIVILEGES_CLASS_NAME);
        $obj_member_privileges->getSingleList('mp_privilege', array('mp_member' => $f_member));
        $result = ($f_restrict) ? !in_array($f_privilege, $this->memberPrivileges) : in_array($f_privilege, $this->memberPrivileges);

        if ($result) {
            return true;
        } else {
            if ($f_redirect) {
                header('location: ' . $this->redirectURL);
            } else {
                return false;
            }
        }
    }

    public function setMemberPrivileges($fa_member_privileges) {
        $this->memberPrivileges = $fa_member_privileges;
    }

    public function getMemberPrivileges($fa_member_privileges) {
        return $this->memberPrivileges;
    }

    public function setRedirectURL($f_redirect_url) {
        $this->redirectURL = $f_redirect_url;
    }

    public function getRedirectURL() {
        return $this->redirectURL;
    }

    public function postValidation() {
        $this->clearCaptcha();
    }

    public function clearCaptcha() {
        if (!empty($_POST[TURING_POST_NAME])) {
            unset($_POST[TURING_POST_NAME]);
        }
        return true;
    }

    public function debug_initializeUser($f_session_ip_name=false) {
        $debug_session_name = ($f_session_ip_name) ? $f_session_ip_name : MANAGER_DEBUG_SESSION_NAME;

        return $_SESSION[$debug_session_name] = $_SERVER['REMOTE_ADDR'];
    }

    public function debug_pageDisplayOutput($f_variable_for_output, $f_explicit_page, $f_pre_tags=true, $f_show_to_ip=false) {

        if ($f_explicit_page == false || $this->templateName == $f_explicit_page) {
            return $this->debug_displayOutput($f_variable_for_output, $f_pre_tags, $f_show_to_ip);
        }
        return false;
    }

    public function debugOut($f_variable_for_output, $f_pre_tags=true, $f_show_to_ip=false) {
        return $this->debug_displayOutput($f_variable_for_output, $f_pre_tags, $f_show_to_ip);
    }

    public function debug_displayOutput($f_variable_for_output, $f_pre_tags=true, $f_show_to_ip=false) {
        $outPut = $this->debug_getOutput($f_variable_for_output, $f_pre_tags, $f_show_to_ip);
        if ($outPut) {
            echo $outPut;
            return $outOut;
        } else {
            return false;
        }
    }

    public function debug_getOutput($f_variable_for_output, $f_pre_tags=true, $f_show_to_ip=false) {
        $showToIPFinal = ($f_show_to_ip) ? $f_show_to_ip : $_SESSION[MANAGER_DEBUG_SESSION_NAME];
        if (!empty($showToIPFinal) && $_SERVER['REMOTE_ADDR'] == $showToIPFinal) {
            $outPut = '';
            if ($f_pre_tags) {
                $outPut .= '<pre>';
            }
            if (is_array($f_variable_for_output) || $f_variable_for_output === false) {
                $outPut .= var_export($f_variable_for_output, true);
            } else {
                $outPut .= $f_variable_for_output;
            }
            if ($f_pre_tags) {
                $outPut .= '</pre>';
            }

            return $outPut;
        }
        return false;
    }



}

?>
