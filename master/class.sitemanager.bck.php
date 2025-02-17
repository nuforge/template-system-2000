<?PHP

class sitemanager {

    private $connection_info = array('user' => '', 'password' => '', 'dbname' => '');
    private $database_connection;
    private $messageHandler;
    private $redirectURL = '/login.html';
    private $privilegeChecks = array();
    private $memberPrivileges = array();
    private $passCheck;

    public function __construct() {

        DEFINE('MIN_PASSWORD_LENGTH', 6);
        DEFINE('MAX_PASSWORD_LENGTH', 24);

        DEFINE('USERNAME_PATTERN', '<<([a-zA-Z0-9_-]*)>>');
        DEFINE('PASSWORD_PATTERN', '/^[A-Za-z0-9._@!#$+-]+$/');

        DEFINE('JPEG_OUTPUT_QUALITY', 100);
        DEFINE('MAX_LARGE_TEXT_COUNT', 10000);
        DEFINE('MAX_MEDIUM_TEXT_COUNT', 1000);
        DEFINE('MAX_SMALL_TEXT_COUNT', 140);

        DEFINE('PRIVILEGE_CLASS_ANY', 'PRIVILEGE_ANY');
        DEFINE('PRIVILEGE_CLASS_MUST', 'PRIVILEGE_MUST');
        DEFINE('PRIVILEGE_CLASS_ALL', 'PRIVILEGE_ALL');
        DEFINE('PRIVILEGE_CLASS_RESTRICT', 'PRIVILEGE_RESTRICT');

        $this->messageHandler = new messageHandler('popup');
        //$this->database_connection = DB_Connection::connect($this->connection_info);
        $this->initialize();
    }

    public function initialize() {
        return true;
    }

    public function setDatabaseConnection($f_dblink) {
        $this->database_connection = $f_dblink;
    }

    public function getDatabaseConnection() {
        return $this->database_connection;
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

    private function addStatusMessage($f_message, $f_errorType='error') {
        return $this->messageHandler->add($f_errorType, $f_message);
    }

    public function returnStatusMessage($f_errorType='error') {
        return $this->messageHandler->output($f_errorType);
    }
    public function checkHasStatusMessage() {
        return $this->messageHandler->has_message();
    }


    public function checkStatusMessage($f_errorType='error') {
        return $this->messageHandler->status($f_errorType);
    }

    public function loadMemberPrivileges($f_member_id) {
        $t_member_privileges = $this->loadClass('member_privileges');
        $privileges = $t_member_privileges->getSelectList('mp_privilege', 'privilege_unique', false, array('mp_member' => $f_member_id));
        $this->setMemberPrivileges($privileges);
        return $privileges;
    }

    public function addPrivilegeCheck($f_privilegeId, $f_privilege_class = PRIVILEGE_CLASS_MUST) {
        $this->privilegeChecks[$f_privilege_class][] = $f_privilegeId;
        return true;
    }

    public function addMultiplePrivilegeChecks($fa_privilege_ids) {
        foreach ($fa_privilege_ids as $privilege => $class) {
            $this->privilegeChecks[$class][] = $privilege;
        }
        return true;
    }

    public function checkPrivileges($f_member, $f_privilege_class=PRIVILEGE_CLASS_ALL, $f_redirect = true) {

        if (!empty($f_member) && empty($this->privilegeChecks)) {
            return true;
        }
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
        $t_member_privileges = $this->loadClass('member_privileges');
        $t_member_privileges->getSingleList('mp_privilege', array('mp_member' => $f_member));
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

    public function setPageData($fa_page_data) {
        $this->pageData = $fa_page_data;
    }

    public function autoLoginMember() {
        $t_members = $this->loadClass('members');
        return $t_members->auto_login();
    }

    public function loginUser($fa_loginInfo) {
        $t_mem = $this->loadClass('members');
        $fa_loginInfo['password'] = md5($fa_loginInfo['password']);
        if (!$t_mem->login($fa_loginInfo)) {
            $this->addStatusMessage('Incorrect username or password. If trouble persists, please contact support.');
            return false;
        }

        return true;
    }

    public function logoutUser() {
        $t_mem = $this->loadClass('members');
        return $t_mem->logout();
    }

    public function getMember($f_unique) {
        $t_mem = $this->loadClass('members');
        $f_unique = trim(strtolower($f_unique));

        return $t_mem->load(array('mem_unique' => $f_unique));
    }

    public function confirmPassword($f_member, $f_password) {
        $t_mem = $this->loadClass('members');

        $mem = $t_mem->load($f_member);


        if (md5($f_password) == $mem['mem_password']) {
            return true;
        } else {
            $this->addStatusMessage('Invalid Password');
            return false;
        }
    }

    public function escapeImages($f_imageData) {
        return str_replace(array("\\\\", "''"), array("\\", "'"), pg_escape_bytea($f_imageData));
    }

    public function validateContactUs($fa_contactPost) {

        if (!PhpCaptcha::Validate($fa_contactPost['code'])) {
            $this->addStatusMessage('Verification Number Invalid.');
        }
        if (empty($fa_contactPost['email'])) {
            $this->addStatusMessage('Please Enter a Value for "Email".');
        }
        if (empty($fa_contactPost['message'])) {
            $this->addStatusMessage('Please Enter a Value for "Message".');
        }

        return!$this->checkStatusMessage();
    }

    public function sendContactUs($fa_contactPost) {

        $email = new sitemanager_email();
        $t_post = $fa_contactPost;
        $t_post['email'] = trim($t_post['email']);
        $t_post['date'] = date('F j, Y');
        $t_post['ip'] = $_SERVER['REMOTE_ADDR'];
        if ($email->sendEmailTemplate('contact', $t_post)) {
            $this->addStatusMessage('Your message has been successfully sent. Thank you.', 'success');
            return true;
        }
        $this->addStatusMessage('An unknown error has occured.');
        return false;
    }

    public function validateSendPasswordReminder($f_email) {
        $f_email = trim($f_email);
        $t_members = $this->loadClass('members');
        //Check email not empty
        if (empty($f_email)) {
            $this->addStatusMessage('Invalid Email. If trouble persists, please contact support.');
            return false;
        }
        $member = $t_members->load(array('mem_email' => $f_email));
        if (empty($member)) {
            $this->addStatusMessage('Invalid Email. If trouble persists, please contact support.');

            return false;
        }

        return true;
    }

    public function sendPasswordReminder($f_email) {
        $f_email = trim($f_email);
        $t_members = $this->loadClass('members');
        $member = $t_members->load(array('mem_email' => $f_email));
        if (empty($member)) {
            $this->addStatusMessage('Invalid Email. If trouble persists, please contact support.');

            return false;
        } else {
            $email = new sitemanager_email();
            $email_contents['email'] = $member['mem_email'];
            $email_contents['code'] = substr(md5(md5($member['member'])), 0, 10);
            $email_contents['phpversion'] = phpversion();
            if ($email->sendEmailTemplate('reminder', $email_contents)) {
                return true;
            } else {

                $this->addStatusMessage('Invalid Email. If trouble persists, please contact support.');
                return false;
            }
        }

        return false;
    }

    public function validateResetPassword($fa_postData) {
        $t_members = $this->loadClass('members');
        //Check Email exists
        $member = $t_members->load(array('mem_email' => $fa_postData['reset_email']));
        if (empty($member)) {
            $this->addStatusMessage('Invalid Email. If trouble persists, please contact support.');
            return false;
        }

        //Check substr(md5(md5(member_id)),0,10) == code
        if ($fa_postData['code'] != substr(md5(md5($member['member'])), 0, 10)) {
            $this->addStatusMessage('Invalid Code. If trouble persists, please contact support.');
            return false;
        }
        //Check password = valid length and makeup
        $fa_postData['new_password'] = trim($fa_postData['new_password']);
        $fa_postData['confirm_password'] = trim($fa_postData['confirm_password']);

        if (empty($fa_postData['new_password'])) {
            $this->addStatusMessage('Please enter a Password');
            return false;
        } else {
            if (!preg_match(PASSWORD_PATTERN, $fa_postData['new_password'])) {
                $this->addStatusMessage('Password format invalid.');
                return false;
            }
            if (strlen($fa_postData['new_password']) < MIN_PASSWORD_LENGTH) {
                $this->addStatusMessage('Password must be atleast 6 characters long');
                return false;
            }
        }
        //Check confirm = password
        if ($fa_postData['new_password'] != $fa_postData['confirm_password']) {
            $this->addStatusMessage('Please confirm your password.');
            return false;
        }
        return true;
    }

    public function resetPassword($fa_postData) {
        $t_members = $this->loadClass('members');
        $member = $t_members->load(array('mem_email' => $fa_postData['reset_email']));
        if (empty($member)) {
            $this->addStatusMessage('Invalid Email. If trouble persists, please contact support.');
            return false;
        }
        return $this->changePassword($member['member'], $fa_postData['new_password'], true);
    }

    public function validateChangePassword($f_new_password, $f_confirm_password) {
        $t_members = $this->loadClass('members');
        //Check Email exists
        //Check password = valid length and makeup
        $f_new_password = trim($f_new_password);
        $f_confirm_password = trim($f_confirm_password);

        if (empty($f_new_password)) {
            $this->addStatusMessage('Please enter a Password');
            return false;
        } else {
            if (!preg_match(PASSWORD_PATTERN, $f_new_password)) {
                $this->addStatusMessage('Password format invalid.');
                return false;
            }
            if (strlen($f_new_password) < MIN_PASSWORD_LENGTH) {
                $this->addStatusMessage('Password must be atleast 6 characters long');
                return false;
            }
        }
        //Check confirm = password
        if ($f_new_password != $f_confirm_password) {
            $this->addStatusMessage('Please confirm your password.');
            return false;
        }
        return true;
    }

    public function changePassword($f_member, $f_password, $f_must_md5=false) {
        $t_members = $this->loadClass('members');
        $f_password = strip_tags(trim($f_password));
        $set['mem_password'] = ($f_must_md5) ? md5($f_password) : $f_password;
        $where['member'] = $f_member;
        if ($t_members->update($set, $where)) {
            $this->addStatusMessage('Password successfully changed.', 'success');
            return true;
        } else {
            $this->addStatusMessage('Unable to change password. Please contact support');
            return false;
        }
    }

}

?>