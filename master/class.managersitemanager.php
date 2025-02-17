<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class ManagerSiteManager extends Manager {

    protected $connection_info;
    protected $database_connection;
    protected $messageHandler;
    protected $redirectURL = '/login.html';
    protected $privilegeChecks = array();
    protected $memberPrivileges = array();
    protected $googleCaptchaCode;
    protected $page;

    protected function initialize_ManagerSiteManager () {
        DEFINE('MANAGER_MEMBERS_CLASS_NAME', 'members');
        DEFINE('MANAGER_MEMBER_PRIVILEGES_CLASS_NAME', 'member_privileges');

        DEFINE('MIN_PASSWORD_LENGTH', 6);
        DEFINE('MAX_PASSWORD_LENGTH', 24);


        DEFINE('MIN_USERNAME_LENGTH', 4);
        DEFINE('MAX_USERNAME_LENGTH', 24);

        DEFINE('MAX_LARGE_TEXT_COUNT', 10000);
        DEFINE('MAX_MEDIUM_TEXT_COUNT', 1000);
        DEFINE('MAX_SMALL_TEXT_COUNT', 140);

        DEFINE('MAX_COMMENT_SUBJECT_SIZE', '64');
        DEFINE('MAX_COMMENT_BODY_SIZE', '1000');

        DEFINE('MAX_PHOTO_FILESIZE', 5000000);
        DEFINE('MAX_PHOTO_FILESIZE_DISPLAY', 5);

        DEFINE('COMMENT_POST_BUFFER', '1 minute');

        return true;
        
    }


    public function setPageData($fa_page_data) {
        $this->page_data = $fa_page_data;
    }

    public function autoLoginMember() {
        $obj_members = $this->loadClass(MANAGER_MEMBERS_CLASS_NAME);
        return $obj_members->auto_login();
    }

    public function validateCaptchaGoogle($f_captcha) {
        if(empty($this->googleCaptchaCode)) {
                $this->addStatusMessage('Captcha Code Setup Incorrectly, Contact Support.');
            return false;}
        $response=json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $this->googleCaptchaCode . "&response=".$f_captcha."&remoteip=".$_SERVER['REMOTE_ADDR']));
        //var_dump($response);
        
        
            if(!$response->success) {
                $this->addStatusMessage('Verification Number Invalid.');
                return false;
            }
        return true;
    }
    
    public function validateGrantMemberPrivilage($f_member, $f_privilege, $f_end=false, $f_start=false) {
        $obj_members = $this->loadClass(MANAGER_MEMBERS_CLASS_NAME);
        $obj_member_privileges = $this->loadClass(MANAGER_MEMBER_PRIVILEGES_CLASS_NAME);
        $obj_privileges = $this->loadClass(MANAGER_PRIVILEGES_CLASS_NAME);

        return true;
    }

    public function grantMemberPrivilege($f_member, $f_privilege, $f_end=false, $f_start=false) {
        $obj_members = $this->loadClass(MANAGER_MEMBERS_CLASS_NAME);
        $obj_member_privileges = $this->loadClass(MANAGER_MEMBER_PRIVILEGES_CLASS_NAME);
        $obj_privileges = $this->loadClass(MANAGER_PRIVILEGES_CLASS_NAME);

        $new_member_privilege['mp_member'] = $f_member;
        $new_member_privilege['mp_privilege'] = $f_privilege;
        if ($f_start) {
            $new_member_privilege['mp_start'] = $f_start;
        }
        if ($f_end) {
            $new_member_privilege['mp_end'] = $f_end;
        }

        if ($obj_member_privileges->insert($new_member_privilege)) {
            $this->addStatusMessage('Added Member Privilege.', MESSAGE_STATUS_TYPE_SUCCESS);
            return true;
        }
        $this->addStatusMessage('Unable to add Member Privilege. Please Contact Support');
        return false;
    }

    public function loginUser($fa_loginInfo) {
        $obj_members = $this->loadClass(MANAGER_MEMBERS_CLASS_NAME);
        $fa_loginInfo['password'] = md5($fa_loginInfo['password']);
        $fa_loginInfo['username'] = strtolower($fa_loginInfo['username']);
        if (!$obj_members->login($fa_loginInfo)) {
            $this->addStatusMessage('Incorrect username or password. If trouble persists, please contact support.');
            return false;
        }

        return true;
    }

    public function logoutUser() {
        $obj_members = $this->loadClass(MANAGER_MEMBERS_CLASS_NAME);
        return $obj_members->logout();
    }

    public function validateContactUs($fa_post_array) {
        if (!PhpCaptcha::Validate($fa_post_array[TURING_POST_NAME])) {
            $this->addStatusMessage('Verification Number Invalid.');
        }
        if (empty($fa_post_array['email_address'])) {
            $this->addStatusMessage('Please Enter a Value for "Email".');
        }
        if (empty($fa_post_array['email_message'])) {
            $this->addStatusMessage('Please Enter a Value for "Message".');
        }
        $this->postValidation();
        return!$this->checkStatusMessage();
    }

    public function sendContactUs($fa_post_array) {
        $email = new email();
        $new_email['email_address'] = trim($fa_post_array['email_address']);
        $new_email['email_message'] = trim($fa_post_array['email_message']);
        $new_email['email_date'] = date('F j, Y');
        $new_email['email_ip'] = $_SERVER['REMOTE_ADDR'];

        $new_email['email_sitename'] = $this->page_data['sitename'];
        if ($email->sendEmailTemplate('contact', $new_email)) {
            $this->addStatusMessage('Your message has been successfully sent. Thank you.', MESSAGE_STATUS_TYPE_SUCCESS);
            return true;
        }
        $this->addStatusMessage('An unknown error has occured.');
        return false;
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

}

?>
