<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/*  TODO Match Algorithm
 *  TODO Code to determine which photos show
 *  TODO Flag phots and Profiles
 *  TODO Suspend Accounts
 *  TODO Suspend/remove Images
 *  TODO Credits
 */

class WebcomicTV_Network_SiteManager extends SiteManager
{

    protected $connection_info = array('user' => 'webcomtv_wctvuser', 'password' => '*84tdRFH!nya', 'dbname' => 'webcomtv_webcomictv');
    protected $database_connection;
    protected $messageHandler;
    protected $redirectURL = '/login.html';
    protected $privilegeChecks = array();
    protected $memberPrivileges = array();
    protected $page;

    protected function initialize_WebcomicTV_Network_SiteManager()
    {

        DEFINE('PRIVILEGE_CLASS_ALL', 'ALL');
        DEFINE('PRIVILEGE_CLASS_ANY', 'ANY');
        DEFINE('PRIVILEGE_CLASS_MUST', 'MUST');
        DEFINE('PRIVILEGE_CLASS_RESTRICT', 'RESTRICT');
        DEFINE('PASSWORD_PATTERN', "/^[A-Za-z0-9._@!#$+-]+$/");

        DEFINE('MIN_REGISTER_AGE', 13);
        DEFINE('MIN_PASSWORD_LENGTH', 6);
        DEFINE('MAX_PASSWORD_LENGTH', 24);
        DEFINE('MAX_LARGE_TEXT_COUNT', 10000);
        DEFINE('MAX_MEDIUM_TEXT_COUNT', 1000);
        DEFINE('MAX_SMALL_TEXT_COUNT', 140);

        DEFINE('MAX_COMMENT_SUBJECT_SIZE', '64');
        DEFINE('MAX_COMMENT_BODY_SIZE', '1000');

        DEFINE('MAX_PHOTO_FILESIZE', 5000000);
        DEFINE('MAX_PHOTO_FILESIZE_DISPLAY', 5);

        DEFINE('COMMENT_TYPE_COMICS', 'comics');
        DEFINE('COMMENT_TYPE_CHARACTERS', 'character');
        DEFINE('COMMENT_TYPE_SITE', 'sites');

        DEFINE('COMMENT_POST_BUFFER', '1 minute');

        DEFINE('MAX_SITE_ICON_FILESIZE', 50000);
        DEFINE('MAX_SITE_ICON_FILESIZE_DISPLAY', 50);

        DEFINE('MAX_COMIC_IMAGE_FILESIZE', 3000000);
        DEFINE('MAX_COMIC_IMAGE_FILESIZE_DISPLAY', 300);



        DEFINE('SITE_ICON_WIDTH', 150);
        DEFINE('SITE_ICON_HEIGHT', 80);



        $this->messageHandler = new messageHandler('popup');

        return $this->reinitialize();
    }

    protected function reinitialize()
    {
        return true;
    }

    public function validateRegisterUser($fa_post_data)
    {
        $t_members = $this->loadClass('members');
        $email_pattern = "/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/";
        $username_pattern = "/^[A-Za-z0-9_]+$/";

        if (empty($fa_post_data['reg_gender']) || ($fa_post_data['reg_gender'] != 'm' && $fa_post_data['reg_gender'] != 'f')) {
            $this->addStatusMessage('Please select a valid year of birth');
        }

        if (empty($fa_post_data['reg_birth_year']) || !is_numeric($fa_post_data['reg_birth_year'])) {
            $this->addStatusMessage('Please select a valid year of birth');
        } else {
            // User must be MIN_REGISTER_AGE years of age or older.
            if ($fa_post_data['reg_birth_year'] > date('Y') - MIN_REGISTER_AGE) {
                $this->addStatusMessage('You Must be alteast ' . MIN_REGISTER_AGE . ' years old to register.');
            }
        }
        // Birth Month must be entered, numeric and between 1 and 12 in value.
        if (empty($fa_post_data['reg_birth_month']) || !is_numeric($fa_post_data['reg_birth_month']) || $fa_post_data['reg_birth_month'] < 1 || $fa_post_data['reg_birth_month'] > 12) {
            $this->addStatusMessage('Invalid Selection for Birth Month.');
        } else {
            $last_day_of_birth_month = date('t', strtotime($fa_post_data['reg_birth_year'] . '-' . $fa_post_data['reg_birth_month'] . '-' . '01'));
        }

        //Birth Day must be entered, numeric, greater than 0 and less than the birth month's number of days.
        if (empty($fa_post_data['reg_birth_day']) || !is_numeric($fa_post_data['reg_birth_day']) || $fa_post_data['reg_birth_day'] < 1 || $fa_post_data['reg_birth_day'] > $last_day_of_birth_month) {
            $this->addStatusMessage('Invalid Birth Day.');
        }


        //Email address must be entered
        if (empty($fa_post_data['reg_email'])) {
            $this->addStatusMessage('Please enter a value for Email');
        } else {
            //Email must match email pattern.
            if (!preg_match($email_pattern, $fa_post_data['reg_email'])) {
                $this->addStatusMessage('Invalid Email Format. Please use another email address.');
                //  Email must be unique in all cases
            } elseif (!$t_members->checkIsUnique($fa_post_data['reg_email'], 'email')) {
                $this->addStatusMessage('Email Address is already registered to another user account.');
            }
        }

        // Confrim email must be entered and match email.
        if ($f_cemail && (empty($fa_post_data['reg_cemail']) || $fa_post_data['reg_cemail'] != $fa_post_data['reg_email'])) {
            $this->addStatusMessage('Value for "Confirm Email" is invalid');
        }

        if (empty($fa_post_data['reg_username'])) {
            $this->addStatusMessage('Please enter a Username.');
        } else {
            if (!$t_members->checkIsUnique($fa_post_data['reg_username'], 'username')) {
                $this->addStatusMessage('Usernames is already registered');
            }
            if (strlen($fa_post_data['reg_username']) < MIN_PASSWORD_LENGTH || strlen($fa_post_data['reg_username']) >= MAX_PASSWORD_LENGTH) {
                $this->addStatusMessage('Usernames Must be between ' . MIN_PASSWORD_LENGTH . '-' . MAX_PASSWORD_LENGTH . ' characters');
            }
            if (!preg_match($username_pattern, $fa_post_data['reg_username'])) {
                $this->addStatusMessage('Usename can only contain the follow: A-Z, a-z, 0-9, and _');
            }
        }
        if (empty($fa_post_data['reg_password'])) {
            $this->addStatusMessage('Please enter a Password');
        } else {
            if (!preg_match(PASSWORD_PATTERN, $fa_post_data['reg_password'])) {
                $this->addStatusMessage('Password format invalid.');
            }
            if (strlen($fa_post_data['reg_password']) < MIN_PASSWORD_LENGTH) {
                $this->addStatusMessage('Password must be atleast 6 characters long');
            }
        }
        if (!PhpCaptcha::Validate($fa_post_data['code'])) {
            $this->addStatusMessage('Verification Number Invalid.');
        }

        $this->postValidation();
        if ($this->checkStatusMessage()) {
            return false;
        }
        return true;
    }

    public function registerUser($fa_post_data)
    {
        $t_members = $this->loadClass('members');
        $member['mem_gender'] = strip_tags($fa_post_data['reg_gender']);
        $member['mem_birthdate'] = strip_tags($fa_post_data['reg_birth_year'] . '-' . $fa_post_data['reg_birth_month'] . '-' . $fa_post_data['reg_birth_day']);
        $member['mem_email'] = strip_tags(trim($fa_post_data['reg_email']));
        $member['mem_username'] = strip_tags(trim($fa_post_data['reg_username']));
        $member['mem_firstname'] = strip_tags(trim($fa_post_data['reg_firstname']));
        $member['mem_lastname'] = strip_tags(trim($fa_post_data['reg_lastname']));
        $member['mem_unique'] = strtolower(strip_tags(trim($fa_post_data['reg_username'])));
        $member['mem_password'] = md5(trim($fa_post_data['reg_password']));
        if ($t_members->insert($member)) {
            $new_email['email'] = $member['mem_email'];
            $new_email['username'] = $member['mem_username'];
            $new_email['password'] = trim($fa_post_data['reg_password']);
            $this->sendRegistrationEmail($new_email);
            return true;
        } else {
            $this->addStatusMessage('Unable to register account. Please contact support.');
            return false;
        }
    }

    public function sendRegistrationEmail($fa_email_data)
    {
        $email = new email();
        if ($email->sendEmailTemplate('register', $fa_email_data)) {
            return true;
        }
    }

    public function validateSendPasswordReminder($f_email)
    {
        $t_members = $this->loadClass('members');
        //Check email not empty
        if (empty($f_email)) {
            $this->addStatusMessage('Invalid Email. If trouble persists, please contact support.');
            return false;
        }

        return true;
    }

    public function sendPasswordReminder($f_email)
    {
        $t_members = $this->loadClass('members');
        $member = $t_members->load(array('mem_email' => $f_email));
        if (empty($member)) {
            $this->addStatusMessage('Invalid Email. If trouble persists, please contact support.');

            return false;
        } else {
            $email = new email();
            $email_contents['email'] = $member['mem_email'];
            $email_contents['code'] = substr(md5(md5($member['member'])), 0, 10);
            if ($email->sendEmailTemplate('reminder', $email_contents)) {
                return true;
            } else {

                $this->addStatusMessage('Invalid Email. If trouble persists, please contact support.');
                return false;
            }
        }

        return false;
    }

    public function validateResetPassword($fa_postData)
    {
        $t_members = $this->loadClass('members');
        //Check Email exists
        $member = $t_members->load(array('mem_email' => $fa_postData['reset_email']));
        if (empty($member)) {
            $this->addStatusMessage('Invalid Email. If trouble persists, please contact support.');
            return false;
        }

        //Check substr(md5(md5(member_id)),0,10) == code
        if ($fa_postData['code'] != substr(md5(md5($member['member'])), 0, 10)) {
            echo '28c8edde3d' . ' == ' . $fa_postData['code'] . ' == ' . substr(md5(md5($member['member'])), 0, 10);
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

    public function resetPassword($fa_postData)
    {
        $t_members = $this->loadClass('members');
        $member = $t_members->load(array('mem_email' => $fa_postData['reset_email']));
        if (empty($member)) {
            $this->addStatusMessage('Invalid Email. If trouble persists, please contact support.');
            return false;
        }
        return $this->changePassword($member['member'], md5($fa_postData['new_password']));
    }

    public function changePassword($f_member, $f_password, $f_must_md5 = false)
    {
        $t_members = $this->loadClass('members');
        $f_password = strip_tags(trim($f_password));
        $set['mem_password'] = ($f_must_md5) ? md5($f_password) : $f_password;
        $where['member'] = $f_member;
        return $t_members->update($set, $where);
    }

    public function updateProfileValidation($fa_post_data, $f_member)
    {
        $t_members = $this->loadClass('members');
        $t_zip = $this->loadClass('zips');
        $t_countries = $this->loadClass('countries');
        //$email_pattern = "/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/";
        //$username_pattern = "/^[A-Za-z0-9_]+$/";
        //$password_pattern = "/^[A-Za-z0-9._@!#$+-]+$/";

        $member = $t_members->load($f_member);
        if (empty($member)) {
            $this->addStatusMessage('Unable to load member. Please contact support.');
            return false;
        }
        if (($fa_post_data['mem_visible'] != '1' && $fa_post_data['mem_visible'] != '0')) {
            $this->addStatusMessage('Please select a valid value for visibility');
        }
        if (($fa_post_data['mem_settings_age_visible'] != '1' && $fa_post_data['mem_settings_age_visible'] != '0')) {
            $this->addStatusMessage('Please select a valid value for age visibility');
        }
        if (($fa_post_data['mem_settings_location_visible'] != '1' && $fa_post_data['mem_settings_location_visible'] != '0')) {
            $this->addStatusMessage('Please select a valid value for location visibility');
        }
        if (empty($fa_post_data['mem_gender']) || ($fa_post_data['mem_gender'] != 'm' && $fa_post_data['mem_gender'] != 'f')) {
            $this->addStatusMessage('Please select a valid year of birth');
        }

        if (empty($fa_post_data['mem_birth_year']) || !is_numeric($fa_post_data['mem_birth_year'])) {
            $this->addStatusMessage('Please select a valid year of birth');
        } else {
            // User must be MIN_REGISTER_AGE years of age or older.
            if ($fa_post_data['mem_birth_year'] > date('Y') - MIN_REGISTER_AGE) {
                $this->addStatusMessage('You Must be alteast ' . MIN_REGISTER_AGE . ' years old to register.');
            }
        }
        // Birth Month must be entered, numeric and between 1 and 12 in value.
        if (empty($fa_post_data['mem_birth_month']) || !is_numeric($fa_post_data['mem_birth_month']) || $fa_post_data['mem_birth_month'] < 1 || $fa_post_data['mem_birth_month'] > 12) {
            $this->addStatusMessage('Invalid Selection for Birth Month.');
        } else {
            $last_day_of_birth_month = date('t', strtotime($fa_post_data['mem_birth_year'] . '-' . $fa_post_data['mem_birth_month'] . '-' . '01'));
        }

        //Birth Day must be entered, numeric, greater than 0 and less than the birth month's number of days.
        if (empty($fa_post_data['mem_birth_day']) || !is_numeric($fa_post_data['mem_birth_day']) || $fa_post_data['mem_birth_day'] < 1 || $fa_post_data['mem_birth_day'] > $last_day_of_birth_month) {
            $this->addStatusMessage('Invalid Birth Day.');
        }

        if (empty($fa_post_data['mem_country']) || !$t_countries->load(array('is' => $fa_post_data['mem_country']))) {
            $this->addStatusMessage('Country Does not Exist in Database. Contact Support.');
        }

        // Zip Code must be entered, numeric, less than 5 character.
        if ($fa_post_data['mem_country'] == 'US') {
            if (empty($fa_post_data['mem_zip']) || !is_numeric($fa_post_data['mem_zip']) || strlen($fa_post_data['mem_zip']) > 5) {
                $this->addStatusMessage('Invalid Zip Code');
            } else {
                // Zip code must be in database.
                if (!$t_zip->load(array('zip' => $fa_post_data['mem_zip']))) {
                    $this->addStatusMessage('Zip Code Does not Exist in Database. Contact Support.');
                }
            }
        } else {
            if (empty($fa_post_data['mem_custom_city']) || strlen($fa_post_data['mem_custom_city']) > 64) {
                $this->addStatusMessage('Please enter a value for city (No greater than 64 characters).');
            }
        }

        //CHECK stats is 1 or 0
        if ($fa_post_data['mem_single'] != 1 && $fa_post_data['mem_single'] != 0) {
            $this->addStatusMessage('Invalid Selection for "Single"');
        }

        //CHECK available is 1 or 0
        if ($fa_post_data['mem_available'] != 1 && $fa_post_data['mem_available'] != 0) {
            $this->addStatusMessage('Invalid Selection for "Available"');
        }
        //CHECK Quote < 140 characters
        if (!empty($fa_post_data['mem_quote']) && strlen($fa_post_data['mem_quote']) > 140) {
            $this->addStatusMessage('Quote Length must be less than 140 characters.');
        }
        //CHECK About >= 500 characters
        if (!empty($fa_post_data['mem_about']) && strlen($fa_post_data['mem_about']) > 500) {
            $this->addStatusMessage('About Me must be less than 500 characters.');
        }


        if ($this->checkStatusMessage()) {
            return false;
        }
        return true;
    }

    public function validateUpdateProfileStatus($f_new_status, $f_member)
    {
        $t_members = $this->loadClass('members');
        $member = $t_members->load($f_member);
        if (empty($member)) {
            $this->addStatusMessage('Unable to load member. Please contact support.');
            return false;
        }
        $f_new_status = strip_tags(trim($f_new_status));
        if ($f_new_status > MAX_SMALL_TEXT_COUNT) {
            $this->addStatusMessage('New Status too long. Must be ' . MAX_SMALL_TEXT_COUNT . ' Characters or less.');
            return false;
        }
        return true;
    }

    public function updateProfileStatus($f_new_status, $f_member)
    {
        $t_members = $this->loadClass('members');
        $member = $t_members->load($f_member);
        if (empty($member)) {
            $this->addStatusMessage('Unable to load member. Please contact support.');
            return false;
        }
        $new_values['mem_status'] = strip_tags(trim($f_new_status));
        $where['member'] = $member['member'];
        if ($t_members->update($new_values, $where)) {
            $this->addStatusMessage('Profile Information updated successfully.', 'success');
            return true;
        } else {
            $this->addStatusMessage('Unable to update account. Please contact support.');
            return false;
        }
    }

    public function updateProfile($fa_post_data, $f_member)
    {
        $t_members = $this->loadClass('members');
        $member = $t_members->load($f_member);
        if (empty($member)) {
            $this->addStatusMessage('Unable to load member. Please contact support.');
            return false;
        }
        $new_values['mem_gender'] = strip_tags($fa_post_data['mem_gender']);
        $new_values['mem_birthdate'] = strip_tags($fa_post_data['mem_birth_year'] . '-' . $fa_post_data['mem_birth_month'] . '-' . $fa_post_data['mem_birth_day']);

        $new_values['mem_quote'] = strip_tags(trim($fa_post_data['mem_quote']));
        $new_values['mem_about'] = strip_tags(trim($fa_post_data['mem_about']));
        $new_values['mem_single'] = strip_tags(trim($fa_post_data['mem_single']));
        $new_values['mem_available'] = strip_tags(trim($fa_post_data['mem_available']));
        $new_values['mem_visible'] = strip_tags(trim($fa_post_data['mem_visible']));
        $new_values['mem_settings_age_visible'] = strip_tags(trim($fa_post_data['mem_settings_age_visible']));
        $new_values['mem_settings_location_visible'] = strip_tags(trim($fa_post_data['mem_settings_location_visible']));
        $new_values['mem_country'] = strip_tags(trim($fa_post_data['mem_country']));
        $new_values['mem_zip'] = strip_tags($fa_post_data['mem_zip']);
        $new_values['mem_custom_city'] = strip_tags($fa_post_data['mem_custom_city']);

        $where['member'] = $member['member'];
        if ($t_members->update($new_values, $where)) {
            $this->addStatusMessage('Profile Information updated successfully.', 'success');
            return true;
        } else {
            $this->addStatusMessage('Unable to update account. Please contact support.');
            return false;
        }
    }

    public function loadProfileByUsername($f_username)
    {
        $t_members = $this->loadClass('members');
        $t_site_credits = $this->loadClass('site_credits');
        $t_favorites = $this->loadClass('favorites');

        $member = $t_members->load(array('mem_unique' => strtolower($f_username)));
        if (!empty($member)) {
            $member['credits'] = $t_site_credits->getList('site_title', array('sc_member' => $member['member']));
            $member['favorites'] = $t_favorites->getList('site_title', array('favorite_member' => $member['member']));
        }
        return $member;
    }

    public function loadProfile($f_member)
    {
        $t_members = $this->loadClass('members');
        $t_site_credits = $this->loadClass('site_credits');

        $member = $t_members->load($f_member);
        if (!empty($member)) {
            $member['credits'] = $t_site_credits->getList('site_credit', array('sc_site' => $site['site']));
        }
        return $member;
    }

    public function hideProfile($f_member) {}

    public function loginUser($fa_loginInfo)
    {
        $t_members = $this->loadClass('members');
        $fa_loginInfo['password'] = md5($fa_loginInfo['password']);
        $fa_loginInfo['username'] = strtolower($fa_loginInfo['username']);
        if (!$t_members->login($fa_loginInfo)) {
            $this->addStatusMessage('Incorrect username or password. If trouble persists, please contact support.');
            return false;
        }

        return true;
    }

    public function logoutUser()
    {
        $t_members = $this->loadClass('members');
        return $t_members->logout();
    }

    public function countMembersList($f_where)
    {
        $t_members = $this->loadClass('members');

        return $t_members->getCount("member", $f_where);
    }

    public function getMembersList($f_where, $f_page_number = 1, $f_show_per_page = 25)
    {
        $t_members = $this->loadClass('members');
        $offset = ($f_page_number && $f_show_per_page) ? (($f_page_number - 1) * $f_show_per_page) : false;

        return $t_members->getList("mem_unique", $f_where, $f_show_per_page, $offset);
    }

    public function getSimpleMemberList($f_where, $f_column = 'mem_username')
    {
        $t_members = $this->loadClass('members');

        return $t_members->getSimpleList($f_column, 'mem_unique', $f_where);
    }

    public function loadWebcomic($f_webcomic)
    {
        $t_webcomics = $this->loadClass('webcomics');
        $where = (is_numeric($f_webcomic)) ? $f_webcomic : array('wc_encoded' => $f_webcomic);
        return $t_webcomics->load($where);
    }

    public function loadWebcomicByEncoded($f_comic_encoded)
    {
        $t_webcomics = $this->loadClass('webcomics');

        $webcomic = $t_webcomics->load(array('wc_encoded' => $f_comic_encoded));
        if (empty($webcomic)) {
            return false;
        }
        $webcomic['credits'] = $this->getWebcomicCreditsByWebcomic($webcomic['webcomic']);
        $webcomic['avatars'] = $this->getAvatarsByWebcomic($webcomic['webcomic']);
        return $webcomic;
    }

    public function countComics($f_where)
    {
        $t_comic = $this->loadClass('comics');

        return $t_comic->getCount('comic', $f_where);
    }

    public function countComicsByWebcomic($f_webcomic)
    {
        $t_comic = $this->loadClass('comics');

        return $t_comic->getCount('comic', array('comic_webcomic' => $f_webcomic));
    }

    public function getNextFreeComicNumber($f_webcomic)
    {
        $t_comics = $this->loadClass('comics');
        $t_webcomics = $this->loadClass('webcomics');
        $webcomic = $t_webcomics->load($f_webcomic);
        if (empty($webcomic)) {
            return false;
        }

        return $t_comics->getNextFreeComicNumber($webcomic['webcomic']);
    }

    public function getChaptersByWebcomic($f_webcomic)
    {
        $t_chapters = $this->loadClass('chapters');
        $t_webcomics = $this->loadClass('webcomics');
        $webcomic = $t_webcomics->load($f_webcomic);
        if (empty($webcomic)) {
            return false;
        }

        $where['chapter_webcomic'] = $webcomic['webcomic'];


        return $t_chapters->getSimpleList('chapter_title', 'chapter', $where);
    }

    public function getComicList($f_where, $f_page_number = 1, $f_show_per_page = 10)
    {
        $t_comics = $this->loadClass('comics');
        $offset = (($f_page_number - 1) * $f_show_per_page);
        return $t_comics->getList(array('comic_stamp' => 'desc'), $f_where, $f_show_per_page, $offset);
    }

    public function getComicListByWebcomic($f_webcomic, $f_page_number = 1, $f_show_per_page = 10)
    {
        $t_comics = $this->loadClass('comics');
        $offset = (($f_page_number - 1) * $f_show_per_page);
        return $t_comics->getList(array('comic_stamp' => 'desc'), array('comic_webcomic' => $f_webcomic), $f_show_per_page, $offset);
    }

    public function getWebcomicList($fa_order, $fa_where)
    {
        $t_webcomics = $this->loadClass('webcomics');
        return $t_webcomics->getList($fa_order, $fa_where);
    }

    public function loadComic($f_where)
    {
        $t_comics = $this->loadClass('comics');

        $comic = $t_comics->load($f_where);
        return $comic;
    }

    public function getLatestComic($f_webcomic = false)
    {
        $t_comics = $this->loadClass('comics');
        $and = '';
        if (!empty($f_webcomic) && is_numeric($f_webcomic)) {
            $and = ' AND comic_webcomic = ' . $f_webcomic . ' ';
        }
        return $t_comics->load(' comic_stamp <= NOW() ' . $and, array('comic_stamp' => 'desc'));
    }

    public function validateAddComic($fa_post_data, $fa_imageFileData, $f_fileName, $f_member)
    {

        // CHECK member has privilages
        // CHECK webcomic exists
        // Check comic does NOT exits
        // Check all fields entered
        // Check Image values
        $t_webcomics = $this->loadClass('webcomics');
        $t_members = $this->loadClass('members');
        if (empty($f_member)) {
            $this->addStatusMessage('You must be logged in to edit site details');
            return false;
        }
        $mem = $t_members->load($f_member);

        if (empty($mem)) {
            $this->addStatusMessage('Invalid member. Please contact support.');
        }

        if (empty($fa_post_data['comic_webcomic']) || !$t_webcomics->load($fa_post_data['comic_webcomic'])) {
            $this->addStatusMessage('Invalid webcomic. Please contact support.');
        }

        if (empty($fa_post_data['comic_title'])) {
            $this->addStatusMessage('Please enter a Comic Title');
        }
        if (empty($fa_post_data['comic_number'])) {
            $this->addStatusMessage('Please enter a Comic Number');
        }
        if ($this->checkStatusMessage()) {
            return false;
        }
        return $this->validateUploadComicImage($fa_imageFileData, $f_fileName, $f_member);
    }

    public function validateUploadComicImage($fa_imageFileData, $f_fileName)
    {
        $image_types = array('image/png', 'image/jpeg', 'image/pjpeg', 'image/gif', 'image/pjpg', 'image/jpg', 'image/bmp', 'image/tiff');
        $blacklist = array(".php", ".phtml", ".php3", ".php4", ".js", ".shtml", ".pl", ".py");
        if (array_search($fa_imageFileData[$f_fileName]['type'], $image_types) === false) {
            $this->addStatusMessage('Invalid file type: ' . $fa_imageFileData[$f_fileName]['type']);
            return false;
        }
        //Check Photo is image
        $size = getimagesize($fa_imageFileData[$f_fileName]['tmp_name']);
        if (!$size) {
            $this->addStatusMessage('Image not valid.');
            return false;
        }
        list($width, $height, $type, $attr) = $size;
        //Check Photo Dimensions
        foreach ($blacklist as $file) {
            if (preg_match("/$file\$/i", $fa_imageFileData[$f_fileName]['name'])) {
                $this->addStatusMessage('Invalid image file type.');
                return false;
            }
        }
        if (filesize($fa_imageFileData[$f_fileName]['tmp_name']) > MAX_COMIC_IMAGE_FILESIZE) {
            $this->addStatusMessage('Filesize too large. Images must be under ' . MAX_COMIC_IMAGE_FILESIZE_DISPLAY . 'KB.');
            return false;
        }
        return true;
    }

    public function addComic($fa_post_data, $f_form_name, $f_member)
    {
        $t_comics = $this->loadClass('comics');
        $t_webcomics = $this->loadClass('webcomics');
        $t_members = $this->loadClass('members');

        $member = $t_members->load($f_member);
        if (empty($member)) {

            $this->addStatusMessage('Invalid Member');
            return false;
        }
        $webcomic = $t_webcomics->load($fa_post_data['comic_webcomic']);
        if (empty($webcomic)) {

            $this->addStatusMessage('Invalid Webcomic');
            return false;
        }

        $new_comic['comic_webcomic'] = strip_tags(trim($fa_post_data['comic_webcomic']));
        $new_comic['comic_number'] = strip_tags(trim($fa_post_data['comic_number']));
        $new_comic['comic_title'] = strip_tags(trim($fa_post_data['comic_title']));
        $new_comic['comic_description'] = strip_tags(trim($fa_post_data['comic_description']));

        $new_comic['comic_stamp'] = (!empty($fa_post_data['comic_stamp'])) ? date('Y-m-d H:i:s', strtotime($fa_post_data['comic_stamp'])) : date('Y-m-d H:i:s');
        $new_comic['comic_transcription'] = strip_tags(trim($fa_post_data['comic_transcription']));

        $new_comic['comic_alt'] = $new_comic['comic_title'] . ' - ' . $new_comic['comic_description'];

        $type = explode('/', $_FILES[$f_form_name]['type']);
        if ($type[1] == 'pjpeg') {
            $type[1] == 'jpeg';
        }
        $fileName = 'comic-' . $new_comic['comic_number'] . '-' . date('Y-m-d', strtotime($new_comic['comic_stamp']));
        $new_comic['comic_filename'] = $fileName . '.' . $type[1];

        if ($fa_post_data['comic_chapter']) {
            $new_comic['comic_chapter'] = strip_tags(trim($fa_post_data['comic_chapter']));
        }
        $new_comic['comic_show_ads'] = ($fa_post_data['comic_show_ads']) ? 'true' : 'false';


        $comicId = $t_comics->insert_return($new_comic);

        if ($comicId) {
            $this->updateComicTags($fa_post_data['comic_tags'], $comicId);
            $t_comics->updateTextSearch($comicId);
            $this->addCommentary($fa_post_data['commentary_body'], $comicId, $member['member']);
            $this->addStatusMessage('Comic Added', 'success');

            if ($this->uploadComicImage($f_form_name, $comicId, $fileName)) {
                $this->addStatusMessage('Comic Image Uploaded', 'success');
                return true;
            } else {
                $this->addStatusMessage('Unable to upload icon.');
            }
        } else {
            $this->addStatusMessage('Unknown Error has Occured.');
        }
        return false;
    }

    public function updateComicTags($f_tag_string, $f_comic)
    {
        $t_tags = $this->loadClass('tags');

        if (!$t_tags->deleteComicTags($f_comic)) {
            $this->addStatusMessage('Unable to delete Comic tags.');
            return false;
        }

        $new_tag['tag_comic'] = $f_comic;
        if (!empty($f_tag_string)) {
            $fa_tags = explode(',', $f_tag_string);
            foreach ($fa_tags as $tag) {
                $new_tag['tag_name'] = strtolower(trim($tag));
                $new_tag['tag_unique'] = $this->encodeString(strtolower(trim($tag)));
                if (!$t_tags->insert($new_tag)) {
                    $this->addStatusMessage('Unable to add comic tags.');
                    return false;
                }
            }
        }
        return true;
    }

    public function uploadComicImage($f_form_name, $f_comic, $f_filename = false)
    {
        $t_comics = $this->loadClass('comics');
        $comic = $t_comics->load($f_comic);

        $fileName = ($f_filename) ? $f_filename : $comic['comic_filename'];

        if (!$comic) {
            $this->addStatusMessage('Invalid Comic');
            return false;
        }
        $fileDirectory = $_SERVER['DOCUMENT_ROOT'] . '/images/shows/' . $comic['wc_encoded'] . '/comics/';

        //$iconFileLocation = $fileDirectory . $fileName;
        $t_uh = new UploadHandler($f_form_name, 5000000, false, $fileDirectory);
        return $t_uh->upload_file_rename($f_form_name, $fileName);
    }

    public function addCommentary($f_commentary_body, $f_comic, $f_member)
    {
        $t_comics = $this->loadClass('comics');
        $t_commentaries = $this->loadClass('commentaries');
        $t_members = $this->loadClass('members');

        $member = $t_members->load($f_member);
        if (empty($member)) {

            $this->addStatusMessage('Invalid Member');
            return false;
        }
        $comic = $t_comics->load($f_comic);
        if (empty($comic)) {

            $this->addStatusMessage('Invalid Comic');
            return false;
        }

        $new_commentary['commentary_member'] = $member['member'];
        $new_commentary['commentary_comic'] = $comic['comic'];
        $new_commentary['commentary_stamp'] = $comic['comic_stamp'];
        $new_commentary['commentary_body'] = strip_tags(trim($f_commentary_body));

        return $t_commentaries->insert($new_commentary);
    }

    public function getComicUpdates($f_limit = 4, $f_filter_adult = false)
    {
        $t_comics = $this->loadClass('comics');

        return $t_comics->getLatestUpdates($f_limit, $f_filter_adult);
    }

    public function getWebcomicCreditsByWebcomic($f_webcomic)
    {
        $t_webcomic_credits = $this->loadClass('webcomic_credits');

        return $t_webcomic_credits->getList('webcomic_credit', array('wcc_webcomic' => $f_webcomic));
    }

    public function getWebcomicCreditsByMember($f_member, $f_limit = false)
    {
        $t_webcomic_credits = $this->loadClass('webcomic_credits');

        return $t_webcomic_credits->getList('webcomic_credit', array('wcc_member' => $f_member), $f_limit);
    }

    public function getSimpleWebcomicCreditsByMember($f_member, $f_limit = false)
    {
        $t_webcomic_credits = $this->loadClass('webcomic_credits');

        return $t_webcomic_credits->getWebcomicSimpleList('wc_title', 'wc_title', array('wcc_member' => $f_member, 'wcc_privileges' => 'true'), $f_limit);
    }

    public function getAvatarsByWebcomic($f_webcomic)
    {
        $t_avatars = $this->loadClass('avatars');

        return $t_avatars->getList('avatar_title', array('avatar_webcomic' => $f_webcomic));
    }

    public function applyAvatar($f_avatar, $f_member)
    {
        $t_members = $this->loadClass('members');

        $set['mem_avatar'] = $f_avatar;
        $where['mem_member'] = $f_member;

        return $t_members->update($set, $where);
    }

    public function loadTag($f_tag_unique)
    {
        $t_tags = $this->loadClass('tags');

        return $t_tags->load(array('tag_unique' => $f_tag_unique));
    }

    public function getTagList()
    {
        $t_tags = $this->loadClass('tags');

        return $t_tags->getTagList();
    }

    public function getTags($f_comic)
    {
        $t_tags = $this->loadClass('tags');

        return $t_tags->getList('tag_name', array('tag_comic' => $f_comic));
    }

    public function countTagResults($f_tag_unique)
    {
        $t_tags = $this->loadClass('tags');

        return $t_tags->getCount('tag', array('tag_unique' => $f_tag_unique));
    }

    public function getComicsByTag($f_tag_unique, $f_page_number = 1, $f_show_per_page = 25)
    {
        $t_tags = $this->loadClass('tags');
        $offset = (($f_page_number - 1) * $f_show_per_page);

        return $t_tags->getList(array('tag_comic' => 'desc'), array('tag_unique' => $f_tag_unique), $f_show_per_page, $offset);
    }

    public function getCharacter($f_character)
    {
        $t_character = $this->loadClass('characters');

        return $t_character->load($f_character);
    }

    public function getCommentaries($f_comic)
    {
        $t_commentaries = $this->loadClass('commentaries');

        return $t_commentaries->getList('commentary_stamp', array('commentary_comic' => $f_comic));
    }

    public function getComments($f_id, $f_type = 'comics')
    {
        $t_comments = $this->loadClass('comments');

        return $t_comments->getList(array('comment_stamp' => 'asc'), array('comment_id' => $f_id, 'comment_type' => $f_type));
    }

    public function getComment($f_comment)
    {
        $t_comments = $this->loadClass('comments');

        return $t_comments->load($f_comment);
    }

    public function validatePostComment($fa_comment, $f_type, $f_member)
    {
        $t_members = $this->loadClass('members');
        $t_comments = $this->loadClass('comments');
        //Check member exists
        if (!$t_members->load($f_member)) {
            $this->addStatusMessage('You must be logged in to post a comment.');
            return false;
        }
        if (empty($f_type)) {
            $this->addStatusMessage('Unable to determine comment type. Please contact support.');
            return false;
        }

        if (!empty($fa_comment['comment_subject']) && strlen($fa_comment['comment_subject']) > MAX_COMMENT_SUBJECT_SIZE) {
            $this->addStatusMessage('Subject can be a maximum of 64 characters.');
            return false;
        }

        $t_comment_type = $this->loadClass($f_type);
        //Check id exists

        if (!$t_comment_type->load($fa_comment['comment_id'])) {
            $this->addStatusMessage('Item does not exist. Cannot post comment. Please contact support.');
            return false;
        }
        //check buffer
        //
        if ($t_comments->getCheckPostBuffer($f_member, COMMENT_POST_BUFFER)) {
            $this->addStatusMessage('You must wait ' . COMMENT_POST_BUFFER . ' between posts.');
            return false;
        }
        //Check Body Not Null
        strip_tags($fa_comment['comment_body']);
        trim($fa_comment['comment_body']);
        if (empty($fa_comment['comment_body'])) {
            $this->addStatusMessage('You must have a value for comment body.');
            return false;
        }
        return true;
    }

    public function postComment($fa_comment, $f_type, $f_member)
    {
        $t_comments = $this->loadClass('comments');

        $new_comment['comment_member'] = $f_member;
        $new_comment['comment_type'] = $f_type;
        $new_comment['comment_id'] = strip_tags(trim($fa_comment['comment_id']));
        strip_tags(trim($fa_comment['comment_subject']));
        if (!empty($fa_comment['comment_subject'])) {
            $new_comment['comment_subject'] = strip_tags(trim($fa_comment['comment_subject']));
        }
        $new_comment['comment_body'] = strip_tags(trim($fa_comment['comment_body']));

        if (!$t_comments->insert($new_comment)) {
            $this->addStatusMessage('Unable to post comment. Please contact support.');
            return false;
        } else {
            $this->addStatusMessage('Comment Successfully Posted', 'success');
            return true;
        }
    }

    public function validateEditComment() {}

    public function editComment() {}

    public function getChapterListByWebcomic($f_webcomic)
    {
        $t_chapters = $this->loadClass('chapters');

        return $t_chapters->getSimpleList('chapter_title', 'chapter_number', array('chapter_webcomic' => $f_webcomic));
    }

    public function getCreatorComic($f_comic_number, $f_webcomic)
    {

        $t_com = $this->loadClass('comics');
        return $t_com->creatorLoad(array('comic_number' => $f_comic_number, 'comic_webcomic' => $f_webcomic));
    }

    public function validateEditComic($fa_postData, $f_comic, $f_member)
    {
        $t_comics = $this->loadClass('comics');
        $t_webcomics = $this->loadClass('webcomics');
        $t_members = $this->loadClass('members');
        $t_webcomic_credits = $this->loadClass('webcomic_credits');

        if ($f_comic != $fa_postData['comic']) {
            $this->addStatusMessage('Invalid Comic. Please contact support.');
            return false;
        }
        //Check comic exists
        $comic = $t_comics->load($fa_postData['comic']);
        if (empty($comic)) {
            $this->addStatusMessage('Comic does not exist. Please contact support.');
            return false;
        }

        //Check member exists
        $member = $t_members->load($f_member);
        if (empty($member)) {
            $this->addStatusMessage('Member ID does not exist. Please contact support.');
            return false;
        }

        //check member has credits for webcomic

        if (!$t_webcomic_credits->load(array('wcc_member' => $member['member'], 'wcc_webcomic' => $comic['comic_webcomic'], 'wcc_privileges' => 'true'))) {

            $this->addStatusMessage('You do not have privileges to edit this comic.');
            return false;
        }
        //check title is not null is valid 100chrs
        trim(strip_tags($fa_postData['comic_title']));
        echo strlen($fa_postData['comic_title']);
        if (empty($fa_postData['comic_title']) || strlen($fa_postData['comic_title']) > 100) {
            $this->addStatusMessage('Comic Title must be a string less than 100 characters long.');
            return false;
        }

        //check description not null
        //check description length is valid  140chrs
        trim(strip_tags($fa_postData['comic_description']));
        if (empty($fa_postData['comic_description']) || strlen($fa_postData['comic_description']) > 140) {
            $this->addStatusMessage('Comic Title must be a string less than 140 characters long.');
            return false;
        }

        //check filename length is NOT NULL
        trim(strip_tags($fa_postData['comic_filename']));
        if (empty($fa_postData['comic_filename'])) {
            $this->addStatusMessage('Comic Filename must not be empty.');
            return false;
        }

        //TODO CHECK FILE EXISTS
        //check comic number >0
        if (empty($fa_postData['comic_number']) || !is_numeric($fa_postData['comic_number']) || $fa_postData['comic_number'] < 1) {
            $this->addStatusMessage('Comic Number must be a numeric value greater than 0');
            return false;
        }

        trim(strip_tags($fa_postData['comic_stamp']));
        if (empty($fa_postData['comic_stamp'])) {
            $this->addStatusMessage('Please enter a value for Comic Post Date');
            return false;
        }

        return true;
    }

    public function editComic($fa_postData, $f_comic)
    {
        $t_comics = $this->loadClass('comics');

        $comic = $t_comics->load($f_comic);
        if (empty($comic)) {
            $this->addStatusMessage('Comic does not exist. Please contact support.');
            return false;
        }
        $where['comic'] = $comic['comic'];

        $new_comic['comic_title'] = trim(strip_tags($fa_postData['comic_title']));
        if (!empty($fa_postData['comic_chapter'])) {
            $new_comic['comic_chapter'] = trim(strip_tags($fa_postData['comic_chapter']));
        }
        $new_comic['comic_stamp'] = date('Y-m-d H:i:s', strtotime($fa_postData['comic_stamp']));
        $new_comic['comic_description'] = trim(strip_tags($fa_postData['comic_description']));
        $new_comic['comic_transcription'] = trim(strip_tags($fa_postData['comic_transcription']));
        $new_comic['comic_alt'] = trim(strip_tags($fa_postData['comic_alt']));
        $new_comic['comic_filename'] = trim(strip_tags($fa_postData['comic_filename']));
        if ($t_comics->update($new_comic, $where)) {
            $t_comics->updateTextSearch($comic['comic']);
            $this->addStatusMessage('Comic Successfully Update', 'success');
            return true;
        } else {
            $this->addStatusMessage('Unable to update comic. Please contact support.');
            return false;
        }
    }

    public function reorderComic($f_current_number, $f_new_number)
    {

        /*
         * if ($f_current_number > $f_new_number) {
         *  for (X)
         *      if (X >= $f_new_number && X < $f_current_number) {
         *          X++;
         *      }
         *  }
         * } else {
         *  for(X)
         *      if(X < $f_current_number && X >= $f_current_number) {
         *          X--;
         *      }
         *  }
         * }
         *
         */
    }

    public function validateRemoveSource($f_comic_source, $f_comic, $f_member)
    {
        $t_comics = $this->loadClass('comics');
        $t_comic_sources = $this->loadClass('comic_sources');
        $t_members = $this->loadClass('members');
        $t_webcomic_credits = $this->loadClass('webcomic_credits');

        if ($f_comic != $f_comic) {
            $this->addStatusMessage('Invalid Comic. Please contact support.');
            return false;
        }
        //Check comic exists
        $comic = $t_comics->load($f_comic);
        if (empty($comic)) {
            $this->addStatusMessage('Comic does not exist. Please contact support.');
            return false;
        }

        //Check member exists
        $member = $t_members->load($f_member);
        if (empty($member)) {
            $this->addStatusMessage('Member ID does not exist. Please contact support.');
            return false;
        }
        $source = $t_comic_sources->load(array('comic_source' => $f_comic_source, 'cs_comic' => $f_comic));
        if (empty($source)) {
            $this->addStatusMessage('Comic Source does not exist. Please contact support.');
            return false;
        }

        if (!$t_webcomic_credits->load(array('wcc_member' => $member['member'], 'wcc_webcomic' => $comic['comic_webcomic'], 'wcc_privileges' => 'true'))) {
            $this->addStatusMessage('You do not have privileges to edit this comic.');
            return false;
        }
        return true;
    }

    public function removeSource($f_comic_source, $f_comic)
    {
        $t_comic_sources = $this->loadClass('comic_sources');
        $comic_source = $t_comic_sources->load(array('comic_source' => $f_comic_source, 'cs_comic' => $f_comic));
        if (empty($comic_source)) {
            return false;
        } else {
            return $t_comic_sources->deleteComicSource($f_comic_source);
        }
        return false;
    }

    public function getCategoryList()
    {

        $t_categories = $this->loadClass('categories');
        return $t_categories->getList('category_title');
    }

    public function getSimpleCategoryList()
    {

        $t_categories = $this->loadClass('categories');
        return $t_categories->getSimpleList('category_title', 'category');
    }

    public function loadCategoryByUnique($f_category_unique)
    {

        $t_categories = $this->loadClass('categories');
        return $t_categories->load(array('category_unique' => $f_category_unique));
    }

    public function getSimpleContentRatingList()
    {

        $t_content_ratings = $this->loadClass('content_ratings');
        return $t_content_ratings->getSimpleList('cr_title', 'content_rating');
    }

    public function getSimpleContentRatingClassificationList()
    {

        $t_content_ratings = $this->loadClass('content_ratings');
        return $t_content_ratings->getSimpleList('cr_classification', 'content_rating');
    }

    public function getSimpleLanguageList()
    {

        $t_languages = $this->loadClass('languages');
        return $t_languages->getSimpleList('language_title', 'language');
    }

    public function validateRemoveTag($f_tag, $f_comic, $f_member)
    {
        $t_comics = $this->loadClass('comics');
        $t_tags = $this->loadClass('tags');
        $t_members = $this->loadClass('members');
        $t_webcomic_credits = $this->loadClass('webcomic_credits');

        //Check comic exists
        $comic = $t_comics->load($f_comic);
        if (empty($comic)) {
            $this->addStatusMessage('Comic does not exist. Please contact support.');
            return false;
        }

        //Check member exists
        $member = $t_members->load($f_member);
        if (empty($member)) {
            $this->addStatusMessage('Member ID does not exist. Please contact support.');
            return false;
        }
        $tag = $t_tags->load(array('tag' => $f_tag, 'tag_comic' => $f_comic));
        if (empty($tag)) {
            $this->addStatusMessage('Tag does not exist. Please contact support.');
            return false;
        }

        if (!$t_webcomic_credits->load(array('wcc_member' => $member['member'], 'wcc_webcomic' => $comic['comic_webcomic'], 'wcc_privileges' => 'true'))) {
            $this->addStatusMessage('You do not have privileges to edit this comic.');
            return false;
        }
        return true;
    }

    public function removeTag($f_tag, $f_comic)
    {
        $t_tags = $this->loadClass('tags');
        $tag = $t_tags->load(array('tag' => $f_tag, 'tag_comic' => $f_comic));
        if (empty($tag)) {
            return false;
        } else {
            return $t_tags->deleteTag($tag['tag']);
        }
        return false;
    }

    public function validateAddTag($f_tag, $f_comic, $f_member)
    {
        $t_comics = $this->loadClass('comics');
        $t_tags = $this->loadClass('tags');
        $t_members = $this->loadClass('members');
        $t_webcomic_credits = $this->loadClass('webcomic_credits');

        //Check comic exists
        $comic = $t_comics->load($f_comic);
        if (empty($comic)) {
            $this->addStatusMessage('Comic does not exist. Please contact support.');
            return false;
        }

        //Check member exists
        $member = $t_members->load($f_member);
        if (empty($member)) {
            $this->addStatusMessage('Member ID does not exist. Please contact support.');
            return false;
        }

        $tag = $t_tags->load(array('tag_unique' => $this->encodeString($f_tag), 'tag_comic' => $f_comic));
        if (!empty($tag)) {
            $this->addStatusMessage('Tag Already Exists. Please contact support.');
            return false;
        }

        if (!$t_webcomic_credits->load(array('wcc_member' => $member['member'], 'wcc_webcomic' => $comic['comic_webcomic'], 'wcc_privileges' => 'true'))) {
            $this->addStatusMessage('You do not have privileges to edit this comic.');
            return false;
        }
        return true;
    }

    public function addTag($f_tag, $f_comic)
    {
        $t_tags = $this->loadClass('tags');

        $new_tag['tag_comic'] = $f_comic;
        $new_tag['tag_name'] = trim($f_tag);
        $new_tag['tag_unique'] = $this->encodeString($f_tag);
        $tag_id = $t_tags->insert($new_tag);
        if ($tag_id) {
            $t = $tag_id . '|<a onclick="confirmRemoveTag(' . $tag_id . ',' . $new_tag['tag_comic'] . ',\'' . $new_tag['tag_name'] . '\'); return false;" style="cursor: pointer;"><img src="/images/icons/cancel.png" width="16" height="16" alt="x" title="remove source" align="absmiddle" border="0"  id="tag_' . $new_tag['tag'] . '_image"/></a>  - ' . $new_tag['tag_name'];
            return $t;
        } else {
            return false;
        }
    }

    public function getLinksSimpleList()
    {
        $t_links = $this->loadClass('links');
        return $t_links->getSimpleList('link_title', 'link_title');
    }

    public function getSimpleSiteList($f_unique = true, $f_where = false, $f_column = 'site_title')
    {
        $t_sites = $this->loadClass('sites');

        return ($f_unique) ? $t_sites->getSimpleUniqueList($f_column, "lower(site_title)", $f_where) : $t_sites->getSimpleList($f_column, "lower(site_title)", $f_where);
    }

    public function countSiteList($f_where = false)
    {
        $t_sites = $this->loadClass('sites');

        return $t_sites->getCount('site', $f_where);
    }

    public function getSiteList($f_where, $f_page_number = 1, $f_show_per_page = 25, $f_order = false)
    {
        $t_sites = $this->loadClass('sites');
        $offset = ($f_page_number && $f_show_per_page) ? (($f_page_number - 1) * $f_show_per_page) : false;
        $order = ($f_order) ? $f_order : "lower(site_title)";
        return $t_sites->getList($order, $f_where, $f_show_per_page, $offset);
    }

    public function getSiteReferences($f_site)
    {
        $t_sites = $this->loadClass('sites');
        return $t_sites->getReferenceList($f_site);
    }

    public function getSiteExitList($f_limit = false, $f_start_date = false, $f_end_date = false)
    {
        $t_site_exits = $this->loadClass('site_exits');
        return $t_site_exits->getSiteExits($f_limit, $f_start_date, $f_end_date);
    }

    public function getSiteChildren($f_site)
    {
        $t_sites = $this->loadClass('sites');
        $where['site_parent'] = $f_site;
        return $t_sites->getList('site_title', $where);
    }

    public function loadSite($f_site_unique_id, $f_as_unique = true)
    {
        $t_sites = $this->loadClass('sites');
        $t_site_credits = $this->loadClass('site_credits');
        $t_site_exits = $this->loadClass('site_exits');
        $t_site_links = $this->loadClass('site_links');
        $t_site_awards = $this->loadClass('site_awards');
        $t_site_tags = $this->loadClass('site_tags');
        $t_favorites = $this->loadClass('favorites');
        $t_site_awards = $this->loadClass('site_awards');
        $t_comic_sources = $this->loadClass('comic_sources');

        $site_where = ($f_as_unique) ? array('site_unique' => $f_site_unique_id) : array('site' => $f_site_unique_id);

        $site = $t_sites->load($site_where);
        if (!empty($site)) {
            $site['credits'] = $t_site_credits->getList('site_credit', array('sc_site' => $site['site']));
            $site['references'] = $t_comic_sources->getList('cs_comic', array('src_site' => $site['site']));
            $site['statistics']['favorites'] = $site['site_favorites'];
            $site['statistics']['exits'] = $site['site_exits'];
            $site['tags'] = $t_site_tags->getSingleList('st_tag', 'st_tag', array('st_site' => $site['site']));
            $site['tag_list'] = $t_site_tags->getList('st_tag', array('st_site' => $site['site']));
            $site['awards'] = $t_site_awards->getList('sa_stamp', array('sa_site' => $site['site']));
            $site['links'] = $t_site_links->getList('link_title', array('sl_site' => $site['site']));
            if ($site['tags']) {
                $site['site_tags'] = implode($site['tags'], ', ');
            }
            if ($site['site_parent']) {
                $site['parent'] = $t_sites->load($site['site_parent']);
            }



            $site['children'] = $this->getSiteChildren($site['site']);
            $days_of_week = array('sunday' => 'Sunday', 'monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday');
            foreach ($days_of_week as $day => $day_title) {
                if ($site['site_updates_' . $day]) {
                    $updates[] = $day_title . 's';
                    $site['update_values'] = true;
                    $site['update_days'][$day]['boolean'] = true;
                    $site['update_days'][$day]['value'] = '1';
                    $site['update_days'][$day]['letter'] = substr($day_title, 0, 1);
                    $site['update_days'][$day]['class'] = 'dotw_updates';
                } else {
                    $site['update_days'][$day]['boolean'] = false;
                    $site['update_days'][$day]['value'] = '0';
                    $site['update_days'][$day]['letter'] = substr($day_title, 0, 1);
                    $site['update_days'][$day]['class'] = 'dotw_no_updates';
                }
            }
            $site['updates_on'] = ($updates) ? implode(', ', $updates) : false;
        }

        return $site;
    }

    public function getSitePageNumber($f_site_unique, $f_show_per_page, $f_where = false)
    {
        if ($f_where) {
            $where = 'AND';
            if (is_array($f_where)) {
                $where .= implode(' AND ', $f_where);
            } else {
                $where .= $f_where;
            }
        }
        $q = "SELECT (COUNT(site)/" . $f_show_per_page . " +1) as page_number FROM sites WHERE site_unique < '" . $f_site_unique . "' " . $where . " ;";
        $results = @pg_fetch_assoc(@pg_query($q));
        return $results['page_number'];
    }

    public function getLetterPageNumbers($f_letter, $f_show_per_page, $f_where = false)
    {
        $t_sites = $this->loadClass('sites');


        return $t_sites->getLetterPageNumbers($f_letter, $f_show_per_page, $f_where);
    }

    public function validateAddSite($fa_post_data, $fa_imageFileData, $f_fileName, $f_member)
    {
        $t_sites = $this->loadClass('sites');
        $t_members = $this->loadClass('members');

        if (empty($f_member)) {
            $this->addStatusMessage('You must be logged in to edit site details');
            return false;
        }
        $mem = $t_members->load($f_member);

        if (empty($mem)) {
            $this->addStatusMessage('Invalid member. Please contact support.');
        }

        if (empty($fa_post_data['site_title'])) {
            $this->addStatusMessage('Please enter a Site Title');
        }
        if (empty($fa_post_data['site_url'])) {
            $this->addStatusMessage('Please enter a Site URL');
        }
        if (empty($fa_post_data['site_unique'])) {
            $this->addStatusMessage('Please enter a Site Unique');
        }

        if ($t_sites->load(array('site_unique' => $fa_post_data['site_unique']))) {
            $this->addStatusMessage('Site Unique is already in Database.');
        }
        if ($this->checkStatusMessage()) {
            return false;
        }
        return $this->validateUploadSiteIcon($fa_imageFileData, $f_fileName, $f_member);
    }

    public function addSite($fa_post_data, $f_form_name)
    {
        $t_sites = $this->loadClass('sites');

        $new_site['site_title'] = strip_tags(trim($fa_post_data['site_title']));
        $new_site['site_url'] = strip_tags(trim($fa_post_data['site_url']));
        $new_site['site_unique'] = strip_tags(trim($fa_post_data['site_unique']));

        $new_site['site_category'] = strip_tags(trim($fa_post_data['site_category']));

        if ($fa_post_data['site_description']) {
            $new_site['site_description'] = strip_tags(trim($fa_post_data['site_description']));
        }
        if ($fa_post_data['site_title_short']) {
            $new_site['site_title_short'] = strip_tags(trim($fa_post_data['site_title_short']));
        }
        if ($fa_post_data['site_description_brief']) {
            $new_site['site_description_brief'] = strip_tags(trim($fa_post_data['site_description_brief']));
        }
        if ($fa_post_data['site_rss']) {
            $new_site['site_rss'] = strip_tags(trim($fa_post_data['site_rss']));
        }
        if ($fa_post_data['site_hidden_url']) {
            $new_site['site_hidden_url'] = strip_tags(trim($fa_post_data['site_hidden_url']));
        }


        $new_site['site_language'] = ($fa_post_data['site_language'] == '0') ? null : $fa_post_data['site_language'];
        $new_site['site_parent'] = ($fa_post_data['site_parent'] == '0') ? null : $fa_post_data['site_parent'];

        $new_site['site_content_rating'] = ($fa_post_data['site_content_rating'] == '0') ? null : $fa_post_data['site_content_rating'];
        $new_site['site_content_language'] = ($fa_post_data['site_content_language'] == '0') ? null : $fa_post_data['site_content_language'];
        $new_site['site_content_violence'] = ($fa_post_data['site_content_violence'] == '0') ? null : $fa_post_data['site_content_violence'];
        $new_site['site_content_sex'] = ($fa_post_data['site_content_sex'] == '0') ? null : $fa_post_data['site_content_sex'];
        $new_site['site_content_themes'] = ($fa_post_data['site_content_themes'] == '0') ? null : $fa_post_data['site_content_themes'];

        $new_site['site_recommended'] = ($fa_post_data['site_recommended']) ? 'true' : 'false';
        $new_site['site_active'] = ($fa_post_data['site_active']) ? 'true' : 'false';
        $new_site['site_ended'] = ($fa_post_data['site_ended']) ? 'true' : 'false';
        $new_site['site_adulwebcomic'] = ($fa_post_data['site_adulwebcomic']) ? 'true' : 'false';
        $new_site['site_nsfw'] = ($fa_post_data['site_nsfw']) ? 'true' : 'false';
        $new_site['site_paysite'] = ($fa_post_data['site_paysite']) ? 'true' : 'false';
        $new_site['site_force_awc'] = ($fa_post_data['site_force_awc']) ? 'true' : 'false';
        $new_site['site_adult_only'] = ($fa_post_data['site_adult_only']) ? 'true' : 'false';
        $new_site['site_requires_login'] = ($fa_post_data['site_adult_only']) ? 'true' : 'false';

        $new_site['site_updates_monday'] = ($fa_post_data['site_updates_monday']) ? 'true' : 'false';
        $new_site['site_updates_tuesday'] = ($fa_post_data['site_updates_tuesday']) ? 'true' : 'false';
        $new_site['site_updates_wednesday'] = ($fa_post_data['site_updates_wednesday']) ? 'true' : 'false';
        $new_site['site_updates_thursday'] = ($fa_post_data['site_updates_thursday']) ? 'true' : 'false';
        $new_site['site_updates_friday'] = ($fa_post_data['site_updates_friday']) ? 'true' : 'false';
        $new_site['site_updates_saturday'] = ($fa_post_data['site_updates_saturday']) ? 'true' : 'false';
        $new_site['site_updates_sunday'] = ($fa_post_data['site_updates_sunday']) ? 'true' : 'false';

        $siteId = $t_sites->insert_return($new_site);

        if ($siteId) {

            if ($fa_post_data['site_twitter']) {
                $twitterAccounts = strip_tags(trim($fa_post_data['site_twitter']));
                $twitSplit = explode('|', $twitterAccounts);

                while (!empty($twitSplit) && $twitter_account = array_pop($twitSplit)) {
                    $this->addTwitterAccountLink($siteId, $twitter_account);
                }
            }

            $this->updateSiteTags($fa_post_data['site_tags'], $siteId);
            if ($this->uploadSiteIcon($f_form_name, $siteId)) {
                $this->addStatusMessage('Site Successfully Added.', 'success');
                return $siteId;
            } else {
                $this->addStatusMessage('Unable to upload icon.');
            }
        } else {
            $this->addStatusMessage('Unknown Error has Occured.');
        }
        return false;
    }

    public function addTwitterAccountLink($f_site, $f_twitter_account)
    {
        $t_links = $this->loadClass('links');
        $t_site_links = $this->loadClass('site_links');

        $new_link['link_title'] = '@' . $f_twitter_account;
        $new_link['link_url'] = 'http://twitter.com/#!/' . $f_twitter_account;
        $new_link['link_link_type'] = 4;
        $new_link['link_description'] = 'Twitter Account for ' . $f_twitter_account;
        $link_id = $t_links->insert_return($new_link);
        if (!empty($link_id)) {
            $new_site_link['sl_link'] = $link_id;
            $new_site_link['sl_site'] = $f_site;
            if (!$t_site_links->insert($new_site_link)) {
                $this->addStatusMessage('Unable to add Twitter Account Site Link.');
                return false;
            }
        } else {
            $this->addStatusMessage('Unable to add Twitter Account Link.');
            return false;
        }
    }

    public function addFacebookAccountLink() {}

    public function convertTwitters()
    {
        $t_sites = $this->loadClass('sites');
        $t_links = $this->loadClass('links');
        $t_site_links = $this->loadClass('site_links');

        $q = "INSERT INTO links (link_title, link_url, link_link_type, link_description) SELECT '@' || site_twitter, 'http://twitter.com/#!/'|| site_twitter, 4, 'Twitter Account for ' || site_twitter FROM sites WHERE site_twitter IS NOT NULL;";
        $sites = $t_sites->getList('site_title', array('site_twitter' => 'NOT NULL'));

        foreach ($sites as $site) {
            $twitSplit = explode('|', $site['site_twitter']);
            while (!empty($twitSplit) && $twitter_account = array_pop($twitSplit)) {
                $twitter_list[$twitter_account][] = $site['site'];
            }
        }

        foreach ($twitter_list as $accountName => $sites) {
            $new_link['link_title'] = '@' . $accountName;
            $new_link['link_url'] = 'http://twitter.com/#!/' . $accountName;
            $new_link['link_link_type'] = 4;
            $new_link['link_description'] = 'Twitter Account for ' . $accountName;
            $link_id = $t_links->insert_return($new_link);

            $new_site_link['sl_link'] = $link_id;
            foreach ($sites as $site) {
                $new_site_link['sl_site'] = $site;
                $t_site_links->insert($new_site_link);
            }
        }
    }

    public function validateEditSiteDetails($fa_post_data, $f_site, $f_member)
    {
        $t_sites = $this->loadClass('sites');
        $t_members = $this->loadClass('members');

        if (empty($f_member)) {
            $this->addStatusMessage('You must be logged in to edit site details');
            return false;
        }
        if (empty($f_site) || !($t_sites->load($f_site))) {
            $this->addStatusMessage('Please select a valid Site');
            return false;
        }
        $mem = $t_members->load($f_member);

        if (empty($mem)) {
            $this->addStatusMessage('Invalid member. Please contact support.');
            return false;
        }
        return true;
    }

    public function updateSiteDetails($fa_post_data, $f_site)
    {
        $t_sites = $this->loadClass('sites');
        if (empty($f_site) || !($t_sites->load($f_site))) {
            $this->addStatusMessage('Please select a valid Site');
            return false;
        }
        $fa_post_data['site_recommended'] = ($fa_post_data['site_recommended']) ? 'true' : 'false';
        $fa_post_data['site_active'] = ($fa_post_data['site_active']) ? 'true' : 'false';
        $fa_post_data['site_ended'] = ($fa_post_data['site_ended']) ? 'true' : 'false';
        $fa_post_data['site_adulwebcomic'] = ($fa_post_data['site_adulwebcomic']) ? 'true' : 'false';
        $fa_post_data['site_nsfw'] = ($fa_post_data['site_nsfw']) ? 'true' : 'false';
        $fa_post_data['site_paysite'] = ($fa_post_data['site_paysite']) ? 'true' : 'false';
        $fa_post_data['site_force_awc'] = ($fa_post_data['site_force_awc']) ? 'true' : 'false';
        $fa_post_data['site_updates_monday'] = ($fa_post_data['site_updates_monday']) ? 'true' : 'false';
        $fa_post_data['site_updates_tuesday'] = ($fa_post_data['site_updates_tuesday']) ? 'true' : 'false';
        $fa_post_data['site_updates_wednesday'] = ($fa_post_data['site_updates_wednesday']) ? 'true' : 'false';
        $fa_post_data['site_updates_thursday'] = ($fa_post_data['site_updates_thursday']) ? 'true' : 'false';
        $fa_post_data['site_updates_friday'] = ($fa_post_data['site_updates_friday']) ? 'true' : 'false';
        $fa_post_data['site_updates_saturday'] = ($fa_post_data['site_updates_saturday']) ? 'true' : 'false';
        $fa_post_data['site_updates_sunday'] = ($fa_post_data['site_updates_sunday']) ? 'true' : 'false';

        $fa_post_data['site_title_short'] = ($fa_post_data['site_title_short'] == '') ? null : strip_tags(trim($fa_post_data['site_title_short']));

        if ($fa_post_data['site_title_short'] == '') {
            $fa_post_data['site_title_short'] = null;
        }
        if ($fa_post_data['site_url'] == '') {
            $fa_post_data['site_url'] = null;
        }
        if ($fa_post_data['site_hidden_url'] == '') {
            $fa_post_data['site_hidden_url'] = null;
        }
        if ($fa_post_data['site_rss'] == '') {
            $fa_post_data['site_rss'] = null;
        }

        if ($fa_post_data['site_update_details'] == '') {
            $fa_post_data['site_update_details'] = null;
        }

        if ($fa_post_data['site_parent'] == '0') {
            $fa_post_data['site_parent'] = null;
        }
        if ($fa_post_data['site_content_rating'] == '0') {
            $fa_post_data['site_content_rating'] = null;
        }
        if ($fa_post_data['site_content_violence'] == '0') {
            $fa_post_data['site_content_violence'] = null;
        }
        if ($fa_post_data['site_content_language'] == '0') {
            $fa_post_data['site_content_language'] = null;
        }
        if ($fa_post_data['site_content_sex'] == '0') {
            $fa_post_data['site_content_sex'] = null;
        }
        if ($fa_post_data['site_content_themes'] == '0') {
            $fa_post_data['site_content_themes'] = null;
        }

        if ($t_sites->update($fa_post_data, array('site' => $f_site))) {

            if (!empty($fa_post_data['site_twitter'])) {
                $twitterAccounts = strip_tags(trim($fa_post_data['site_twitter']));
                $twitSplit = explode('|', $twitterAccounts);

                while (!empty($twitSplit) && $twitter_account = array_pop($twitSplit)) {
                    $this->addTwitterAccountLink($f_site, $twitter_account);
                }
            }

            $this->updateSiteTags($fa_post_data['site_tags'], $f_site);
            $t_sites->updateTextSearch($f_site);
            $this->addStatusMessage('Site Editted', 'success');

            return true;
        } else {
            $this->addStatusMessage('Unable to update Site table');
            return false;
        }
        return false;
    }

    public function updateSiteTags($f_tag_string, $f_site)
    {
        $t_site_tags = $this->loadClass('site_tags');

        if (!$t_site_tags->deleteSiteTags($f_site)) {
            $this->addStatusMessage('Unable to delete site tags.');
            return false;
        }

        $new_tag['st_site'] = $f_site;
        if (!empty($f_tag_string)) {
            $fa_tags = explode(',', $f_tag_string);
            foreach ($fa_tags as $tag) {
                $new_tag['st_tag'] = strtolower(trim($tag));
                $new_tag['st_unique'] = $this->encodeString(strtolower(trim($tag)));
                if (!$t_site_tags->insert($new_tag)) {
                    $this->addStatusMessage('Unable to add site tags.');
                    return false;
                }
            }
        }
        return true;
    }

    public function loadSiteTag($f_tag_unique)
    {
        $t_site_tags = $this->loadClass('site_tags');
        return $t_site_tags->load(array('st_unique' => $f_tag_unique));
    }

    public function countSitesByTag($f_tag_unique, $f_where = false)
    {
        $t_site_tags = $this->loadClass('site_tags');
        $where = array('st_unique' => $f_tag_unique);
        if (!empty($f_where)) {
            $where = (is_array($f_where)) ? $f_where + array('st_unique' => $f_tag_unique) : $f_where . " AND st_unique = '" . $f_tag_unique . "' ";
        }
        return $t_site_tags->getCount('site_tag', $where);
    }

    public function getSitesByTag($f_tag_unique, $f_where = false, $f_page_number = 1, $f_show_per_page = 25)
    {
        $t_site_tags = $this->loadClass('site_tags');
        $offset = (($f_page_number - 1) * $f_show_per_page);
        $where = array('st_unique' => $f_tag_unique);
        if (!empty($f_where)) {
            $where = (is_array($f_where)) ? $f_where + array('st_unique' => $f_tag_unique) : $f_where . " AND st_unique = '" . $f_tag_unique . "' ";
        }

        return $t_site_tags->getList(array('st_site' => 'desc'), $where, $f_show_per_page, $offset);
    }

    public function updateSiteTextSearch($f_site = false)
    {
        $t_sites = $this->loadClass('sites');

        return $t_sites->updateTextSearch($f_site);
    }

    public function countSitesBySearch($f_query)
    {
        if (empty($f_query)) {
            return false;
        }
        $t_sites = $this->loadClass('sites');
        $where = "plainto_tsquery('" . $f_query . "') @@ site_tsvector";
        return $t_sites->getCount('site', $where);
    }

    public function getSitesRandomNew($f_recent_show, $f_hide_adult = false)
    {
        $t_sites = new sites();
        $where = false;
        if ($f_hide_adult) {
            $where['site_adult_only'] = 'false';
        }
        return $t_sites->getList(array('site_added' => 'desc'), $where, $f_recent_show);
    }

    public function getSitesBySearch($f_query, $f_page_number = 1, $f_show_per_page = 25)
    {
        if (empty($f_query)) {
            return false;
        }
        $t_sites = $this->loadClass('sites');
        $offset = (($f_page_number - 1) * $f_show_per_page);
        $where = "plainto_tsquery('" . $f_query . "') @@ site_tsvector";
        $order = "ts_rank DESC, site_title ";

        return $t_sites->getSearchList($order, $f_query, $where, $f_show_per_page, $offset);
    }

    public function validateUploadSiteIcon($fa_imageFileData, $f_fileName, $f_member)
    {
        $t_members = $this->loadClass('members');
        $t_sites = $this->loadClass('sites');

        if (empty($f_member)) {
            $this->addStatusMessage('You must be logged in to upload a image');
            return false;
        }
        $mem = $t_members->load($f_member);

        if (empty($mem)) {
            $this->addStatusMessage('Invalid member. Please contact support.');
            return false;
        }

        return $this->validateSiteIconImage($fa_imageFileData, $f_fileName);
    }

    public function validateSiteIconImage($fa_imageFileData, $f_fileName)
    {
        $image_types = array('image/png');
        $blacklist = array(".php", ".phtml", ".php3", ".php4", ".js", ".shtml", ".pl", ".py");
        if (array_search($fa_imageFileData[$f_fileName]['type'], $image_types) === false) {
            $this->addStatusMessage('Invalid file type.');
            return false;
        }
        //Check Photo is image
        $size = getimagesize($fa_imageFileData[$f_fileName]['tmp_name']);
        if (!$size) {
            $this->addStatusMessage('Image not valid.');
            return false;
        }
        list($width, $height, $type, $attr) = $size;
        //Check Photo Dimensions
        if ($width != SITE_ICON_WIDTH || $height != SITE_ICON_HEIGHT) {
            $this->addStatusMessage('Image dimensions must be ' . SITE_ICON_WIDTH . ' x ' . SITE_ICON_HEIGHT . ' pixels.');
            return false;
        }
        foreach ($blacklist as $file) {
            if (preg_match("/$file\$/i", $fa_imageFileData[$f_fileName]['name'])) {
                $this->addStatusMessage('Invalid image file type.');
                return false;
            }
        }
        if (filesize($fa_imageFileData[$f_fileName]['tmp_name']) > MAX_SITE_ICON_FILESIZE) {
            $this->addStatusMessage('Filesize too large. Images must be under ' . MAX_SITE_ICON_FILESIZE_DISPLAY . 'KB.');
            return false;
        }
        return true;
    }

    public function uploadSiteIcon($f_form_name, $f_site)
    {
        $t_sites = $this->loadClass('sites');
        $site = $t_sites->load($f_site);

        if (!$site) {
            $this->addStatusMessage('Invalid Site');
            return false;
        }
        $fileDirectory = $_SERVER['DOCUMENT_ROOT'] . '/images/sites/';
        $fileName = $site['site'] . '-card-' . $site['site_unique'];
        $iconFileLocation = $fileDirectory . $fileName;
        $t_uh = new UploadHandler($f_form_name, 5000000, false, $fileDirectory);
        return $t_uh->upload_file_rename($f_form_name, $fileName);
    }

    public function validateUploadImage($fa_imageFileData, $fa_postData, $f_fileName, $f_webcomic, $f_member)
    {
        $t_members = $this->loadClass('members');
        $t_webcomics = $this->loadClass('webcomics');

        if (empty($f_member)) {
            $this->addStatusMessage('You must be logged in to upload a image');
            return false;
        }
        if (empty($f_webcomic) || !($t_webcomics->load($f_webcomic))) {
            $this->addStatusMessage('Please select a valid Webcomic');
            return false;
        }
        $mem = $t_members->load($f_member);

        if (empty($mem)) {
            $this->addStatusMessage('Invalid member. Please contact support.');
            return false;
        }

        return $this->validateImage($fa_imageFileData, $fa_postData, $f_fileName);
    }

    //TODO Finish Validate IMage
    public function validateImage($fa_imageFileData, $fa_postData, $f_fileName)
    {
        $image_types = array('image/jpeg', 'image/gif', 'image/png', 'image/tiff');
        $blacklist = array(".php", ".phtml", ".php3", ".php4", ".js", ".shtml", ".pl", ".py");
        $sizes = explode('x', $fa_postData['size']);
        if (array_search($fa_imageFileData[$f_fileName]['type'], $image_types) === false) {
            echo $fa_imageFileData[$f_fileName]['type'];
            $this->addStatusMessage('Invalid file type.');
            return false;
        }
        //Check Photo is image
        $size = getimagesize($fa_imageFileData[$f_fileName]['tmp_name']);
        if (!$size) {
            $this->addStatusMessage('Image not valid.');
            return false;
        }
        list($width, $height, $type, $attr) = $size;
        //Check Photo Dimensions
        if ($width != $sizes[0] || $height != $sizes[1]) {
            $this->addStatusMessage('Image dimensions must be ' . $sizes[0] . ' x ' . $sizes[1] . ' pixels.');
            return false;
        }
        foreach ($blacklist as $file) {
            if (preg_match("/$file\$/i", $fa_imageFileData[$f_fileName]['name'])) {
                $this->addStatusMessage('Invalid image file type.');
                return false;
            }
        }
        if (filesize($fa_imageFileData[$f_fileName]['tmp_name']) > MAX_PHOTO_FILESIZE) {
            $this->addStatusMessage('Filesize too large. Images must be under ' . MAX_PHOTO_FILESIZE_DISPLAY . 'MB.');
            return false;
        }
        return true;
    }

    //TODO Finish Upload IMage
    public function moveImage($f_form_name, $f_webcomic_encoded, $f_sizename)
    {
        $up_dir = $_SERVER['DOCUMENT_ROOT'] . '/images/shows/' . $f_webcomic_encoded . '/';
        $file = $up_dir . $f_sizename . '.png';
        $t_uh = new UploadHandler($f_form_name, 5000000, false, $up_dir);
        if (file_exists($file)) {
            //chmod($up_dir, 777);
            //chown($up_dir,'nobody');
            //chmod($file, 777);
            //chown($file,'nobody');
        }
        //$formname=false,$newname=false,$sizelimit=false,$filetypes=false,$uploaddir=false
        $t_uh->upload_file_rename($f_form_name, $f_sizename);
    }

    public function checkForUnreadMessages($f_member)
    {
        $t_con = $this->loadClass('conversations');
        return $t_con->checkForUnread($f_member);
    }

    // TODO Validate message
    public function validateReply($f_message, $f_conversation, $f_member)
    {

        // TODO Ensure not blacklisted
        //Ensure not EMPTY OR too large
        $message = strip_tags(htmlspecialchars($f_message));
        if (empty($message)) {
            $this->addStatusMessage('Message Body is empty.');
        }
        if (strlen($message) > MAX_LARGE_TEXT_COUNT) {
            $this->addStatusMessage('Message body too long. Text must be less than ' . MAX_LARGE_TEXT_COUNT . ' chacaters.');
        }

        return ($this->checkStatusMessage()) ? false : true;
    }

    public function sendReply($f_message, $f_conversation, $f_member)
    {
        $t_con = $this->loadClass('conversations');
        $t_mes = $this->loadClass('messages');
        if (!$t_con->load($f_conversation) || $t_con->checkConversation($f_conversation, $f_member)) {
            $this->addStatusMessage('Invalid Conversation');
            return false;
        }
        $new_message['message_conversation'] = $f_conversation;
        $new_message['message_sender'] = $f_member;
        $new_message['message_body'] = strip_tags(htmlspecialchars($f_message));

        if ($t_mes->insert($new_message)) {
            $this->addStatusMessage('Message successfully sent.');
            return true;
        } else {
            $this->addStatusMessage('Unable to send reply. Please contact support.');
            return false;
        }
    }

    // TODO Validate $this->loadClass('message
    public function validateNewMessage($f_sender, $f_recipient, $f_subject, $f_message)
    {
        $t_mem = $this->loadClass('members');
        //TODO Check Not Blacklisted
        //TODO Check If Match OR paid 100 credits
        //
        //TODO check inbox not full
        // TODO check member is not sending to self
        //Check Subject RIght size
        $subject = strip_tags(htmlspecialchars($f_subject));
        $message = strip_tags(htmlspecialchars($f_message));
        if (strlen($subject) > MAX_SMALL_TEXT_COUNT) {
            $this->addStatusMessage('Message subject must be less than ' . MAX_SMALL_TEXT_COUNT . ' characters.');
            return false;
        }
        //Check Message right size
        if (strlen($message) > MAX_LARGE_TEXT_COUNT) {
            $this->addStatusMessage('Message body must be less than ' . MAX_LARGE_TEXT_COUNT . ' characters.');
            return false;
        }
        if (empty($message)) {
            $this->addStatusMessage('Message body cannot be empty.');
            return false;
        }
        //Check members exist
        if (!$t_mem->load(array('member' => $f_sender))) {
            $this->addStatusMessage('Sender is an invalid member.');
            return false;
        }
        $recip = $t_mem->load(array('mem_unique' => $f_recipient));
        if (!$recip) {
            $this->addStatusMessage('Recipient is an invalid member.');
            return false;
        }

        if (!$this->checkMatchedMembers($f_sender, $recip['member'])) {
            $this->addStatusMessage('You may only message matches or members whom have messaged you.');
            return false;
        }
        $this->addStatusMessage('Message successfully sent.', 'success');
        return true;
    }

    public function sendNewMessage($fa_message_details, $f_sender, $f_recipient)
    {
        //Strip tags str_tags(htmlspecialchars());
        //Create Convo
        $t_con = $this->loadClass('conversations');
        $t_mes = $this->loadClass('messages');
        $t_mem = $this->loadClass('members');
        if (empty($f_recipient)) {
            $this->addStatusMessage('Please select a recipient for this message.');
            return false;
        }
        if (is_numeric($f_recipient)) {
            $recipient = $t_mem->load($f_recipient);
        } else {
            $recipient = $t_mem->load(array('mem_unique' => $f_recipient));
        }
        $new_conversation['con_sender'] = $f_sender;
        $new_conversation['con_recipient'] = $recipient['member'];
        $new_conversation['con_subject'] = strip_tags(htmlspecialchars($fa_message_details['message_subject']));
        $new_message['message_sender'] = $f_sender;
        $new_message['message_body'] = strip_tags(htmlspecialchars($fa_message_details['message_body']));
        $new_message['message_conversation'] = $t_con->insert($new_conversation);
        if (empty($new_message['message_conversation'])) {
            $this->addStatusMessage('Unable to create conversation. Please contact support.');
            return false;
        }

        if (!$t_mes->insert($new_message)) {
            $this->addStatusMessage('Unable to send message. Please contact support.');
            return false;
        }
        return true;
    }

    public function countConversations($f_member)
    {
        $t_con = $this->loadClass('conversations');
        return $t_con->countConversations($f_member);
    }

    public function getConversations($f_member, $f_page_number, $f_show_per_page)
    {
        $t_con = $this->loadClass('conversations');

        return $t_con->getConversations($f_member, $f_page_number, $f_show_per_page);
    }

    public function readConversation($f_conversation, $f_member)
    {
        $t_con = $this->loadClass('conversations');
        return $t_con->readConversation($f_conversation, $f_member);
    }

    public function validateDeleteConversations($fa_conversations, $f_member)
    {
        // Check conversations.
        $t_con = $this->loadClass('conversations');
        foreach ($fa_conversations as $conversation) {
            if ($t_con->checkConversation($conversation, $f_member)) {
                $this->addStatusMessage('Invalid Conversation: ' . $conversation . '. If trouble persists, contact support');
            }
        }
        return true;
    }

    public function deleteConversations($fa_conversations, $f_member)
    {
        $t_con = $this->loadClass('conversations');
        $delcount = 0;
        foreach ($fa_conversations as $conversation) {
            if ($t_con->deleteConversation($conversation, $f_member)) {
                $delcount++;
            } else {
                $this->addStatusMessage('Unable to delete conversation: ' . $conversation . '. If trouble persists, contact support', 'success');
            }
        }
        $this->addStatusMessage($delcount . ' messages deleted.', 'success');
        return true;
    }

    public function encodeString($string, $replace = '-', $lower = true)
    {
        $text = preg_replace("[\-$]", '', preg_replace("[\W+]", $replace, $string));
        if ($lower) {
            return strtolower($text);
        }
        return $text;
    }

    public function exitToSite($f_site_unqiue, $f_member = false, $f_ad = false)
    {
        $t_exits = $this->loadClass('site_exits');
        $t_sites = $this->loadClass('sites');

        $site = $t_sites->load(array('site_unique' => $f_site_unqiue));
        $exit['se_site'] = $site['site'];
        $exit['se_referrer'] = $_SERVER['HTTP_REFERER'];

        if ($f_ad) {
            $exit['se_notes'] = 'ad';
        }

        if ($f_member !== false) {
            $exit['se_member'] = $f_member;
        }
        $exit['se_ip'] = $_SERVER['REMOTE_ADDR'];

        if ($f_member != 1) {
            $t_exits->insert($exit);
        }
        header('location: ' . $site['site_hidden_url']);
    }

    public function getTopSiteExits($f_limit)
    {
        $t_sites = $this->loadClass('sites');
        $t_exits = $this->loadClass('site_exits');
    }

    public function getSimpleAwardList()
    {
        $t_awards = $this->loadClass('awards');
        return $t_awards->getSimpleList('award_title', 'award_title');
    }

    public function getAwardList($f_where = false)
    {
        $t_awards = $this->loadClass('awards');
        return $t_awards->getList('award_title', $f_where);
    }

    public function loadAward($f_award = false)
    {
        $t_awards = $this->loadClass('awards');
        return $t_awards->load($f_award);
    }

    public function validateGiveAward($f_site_unique, $f_award, $_comments, $f_member)
    {
        $t_sites = $this->loadClass('sites');
        $t_awards = $this->loadClass('awards');
        $t_site_awards = $this->loadClass('site_awards');
        $t_members = $this->loadClass('members');

        if (empty($f_member) || !$t_members->load($f_member)) {
            $this->addStatusMessage('You must be logged in to edit site details');
            return false;
        }
        if (empty($f_site_unique)) {
            $this->addStatusMessage('Please select a site.');
            return false;
        }
        $site = $t_sites->load(array('site_unique' => $f_site_unique));
        if (empty($site)) {
            $this->addStatusMessage('Invalid Site.');
            return false;
        }
        if (empty($f_award) || !$t_awards->load($f_award)) {
            $this->addStatusMessage('Invalid Award');
            return false;
        }

        if ($this->checkHasAward($site['site'], $f_award, $f_member)) {
            $this->addStatusMessage('Site already has this award');
            return false;
        }

        return !$this->checkStatusMessage;
    }

    public function checkHasAward($f_site, $f_award, $f_member)
    {
        $t_site_awards = $this->loadClass('site_awards');
        $where['sa_site'] = $f_site;
        $where['sa_award'] = $f_award;
        $where['sa_member'] = $f_member;
        return ($t_site_awards->load($where)) ? true : false;
    }

    public function giveAward($f_site_unique, $f_award, $f_comments, $f_member)
    {
        $t_site_awards = $this->loadClass('site_awards');
        $t_sites = $this->loadClass('sites');
        $site = $t_sites->load(array('site_unique' => $f_site_unique));

        $new_award['sa_site'] = $site['site'];
        $new_award['sa_award'] = $f_award;
        $new_award['sa_member'] = $f_member;
        if (!empty($f_comments)) {
            $new_award['sa_comments'] = strip_tags($f_comments);
        }
        if ($t_site_awards->insert($new_award)) {
            $this->addStatusMessage('Added Award to Site', 'success');
            return true;
        }
        $this->addStatusMessage('Unable to add award. Contact Support.');
        return false;
    }

    public function validateRemoveSiteAward($f_site_award, $f_site_unique, $f_member)
    {
        $t_sites = $this->loadClass('sites');
        $t_site_awards = $this->loadClass('site_awards');
        $t_members = $this->loadClass('members');

        if (empty($f_member) || !$t_members->load($f_member)) {
            $this->addStatusMessage('You must be logged in to edit site details');
            return false;
        }
        if (empty($f_site_unique)) {
            $this->addStatusMessage('Please select a site.');
            return false;
        }
        $site = $t_sites->load(array('site_unique' => $f_site_unique));
        if (empty($site)) {
            $this->addStatusMessage('Invalid Site.');
            return false;
        }
        $site_award = $t_site_awards->load($f_site_award);
        if (empty($site_award)) {
            $this->addStatusMessage('Invalid Site Award.');
            return false;
        }

        if ($site_award['sa_site'] != $site['site']) {
            $this->addStatusMessage('Award does not belong to this site.');
            return false;
        }
        return !$this->checkStatusMessage;
    }

    public function removeSiteAward($f_site_award)
    {
        $t_site_awards = $this->loadClass('site_awards');

        $site_award = $t_site_awards->load($f_site_award);

        if (empty($site_award)) {
            $this->addStatusMessage('Site does not have this award');
            return false;
        }

        return $t_site_awards->removeAward($site_award['site_award']);
    }

    public function validateAssignSiteCredit($fa_post_array, $f_site_unique)
    {
        $t_sites = $this->loadClass('sites');
        $t_members = $this->loadClass('members');

        if (empty($fa_post_array['sc_member']) || !$t_members->load($fa_post_array['sc_member'])) {
            $this->addStatusMessage('You must be logged in to edit site details');
            return false;
        }
        if (empty($f_site_unique)) {
            $this->addStatusMessage('Please select a site.');
            return false;
        }
        $site = $t_sites->load(array('site_unique' => $f_site_unique));
        if (empty($site)) {
            $this->addStatusMessage('Invalid Site.');
            return false;
        }
        return !$this->checkStatusMessage;
    }

    public function assignSiteCredit($fa_post_array, $f_site_unique)
    {
        $t_site_credits = $this->loadClass('site_credits');
        $t_members = $this->loadClass('members');
        $t_sites = $this->loadClass('sites');
        $site = $t_sites->load(array('site_unique' => $f_site_unique));
        $member = $t_members->load($fa_post_array['sc_member']);

        if (empty($member) || empty($site)) {
            return false;
        }
        $new_site_credit['sc_site'] = $site['site'];
        $new_site_credit['sc_member'] = $member['member'];
        $new_site_credit['sc_title'] = strip_tags(trim($fa_post_array['sc_title']));
        if (!empty($fa_post_array['sc_start_date'])) {
            $new_site_credit['sc_start_date'] = date('Y-m-d', strtotime($fa_post_array['sc_start_date']));
        }
        if (!empty($fa_post_array['sc_end_date'])) {
            $new_site_credit['sc_end_date'] = date('Y-m-d', strtotime($fa_post_array['sc_end_date']));
        }
        if (!empty($fa_post_array['sc_descrition'])) {
            $new_site_credit['sc_descrition'] = strip_tags(trim($fa_post_array['sc_descrition']));
        }


        if ($t_site_credits->insert($new_site_credit)) {
            $this->addStatusMessage('Added ' . $member['mem_username'] . ' as ' . $new_site_credit['sc_title'] . ' for ' . $site['site_title'], 'success');
            return true;
        }
        $this->addStatusMessage('Unable to add Site Credit. Contact Support.');
        return false;
    }

    public function validateRemoveSiteCredit($f_site_credit, $f_site_unique, $f_member)
    {
        $t_sites = $this->loadClass('sites');
        $t_site_credits = $this->loadClass('site_credits');
        $t_members = $this->loadClass('members');

        if (empty($f_member) || !$t_members->load($f_member)) {
            $this->addStatusMessage('You must be logged in to edit site details');
            return false;
        }
        if (empty($f_site_unique)) {
            $this->addStatusMessage('Please select a site.');
            return false;
        }
        $site = $t_sites->load(array('site_unique' => $f_site_unique));
        if (empty($site)) {
            $this->addStatusMessage('Invalid Site.');
            return false;
        }
        $site_credit = $t_site_credits->load($f_site_credit);
        if (empty($site_credit)) {
            $this->addStatusMessage('Invalid Site Credit.');
            return false;
        }

        if ($site_credit['sc_site'] != $site['site']) {
            $this->addStatusMessage('Credit does not belong to this site.');
            return false;
        }
        return !$this->checkStatusMessage;
    }

    public function removeSiteCredit($f_site_credit)
    {
        $t_site_credits = $this->loadClass('site_credits');

        $site_credit = $t_site_credits->load($f_site_credit);

        if (empty($site_credit)) {
            $this->addStatusMessage('Site does not have this credit');
            return false;
        }

        if ($t_site_credits->removeSiteCredit($site_credit['site_credit'])) {
            $this->addStatusMessage('Credit removed.', 'success');
            return true;
        } else {
            $this->addStatusMessage('Credit does not belong to this site.');
            return false;
        }
    }

    public function checkIfFavoriteSite($f_site, $f_member)
    {
        $t_favorites = $this->loadClass('favorites');

        return ($t_favorites->load(array('favorite_member' => $f_member, 'favorite_site' => $f_site))) ? true : false;
    }

    public function validateAddSiteToFavorites($f_site, $f_member)
    {
        $t_sites = $this->loadClass('sites');
        $t_favorites = $this->loadClass('favorites');
        $t_members = $this->loadClass('members');

        if (empty($f_member) || !$t_members->load($f_member)) {
            $this->addStatusMessage('You must be logged in to add a site to your favorites.');
            return false;
        }
        if (empty($f_site) || !$t_sites->load(array('site' => $f_site))) {
            $this->addStatusMessage('Invalid Site');
            return false;
        }

        return true;
    }

    public function addSiteToFavorites($f_site, $f_member)
    {
        $t_sites = $this->loadClass('sites');
        $t_favorites = $this->loadClass('favorites');
        $t_members = $this->loadClass('members');

        $new_favorite['favorite_member'] = $f_member;
        $new_favorite['favorite_site'] = $f_site;
        if ($t_favorites->insert($new_favorite)) {
            $this->addStatusMessage('Site added to your favorites.', 'success');
            return true;
        } else {
            $this->addStatusMessage('Unable to favorite Site. Please contact support.');
            return false;
        }
    }

    public function validateRemoveSiteFromFavorites($f_site, $f_member)
    {
        $t_sites = $this->loadClass('sites');
        $t_favorites = $this->loadClass('favorites');
        $t_members = $this->loadClass('members');


        if (empty($f_member) || !$t_members->load($f_member)) {
            $this->addStatusMessage('You must be logged in to add a site to your favorites.');
            return false;
        }
        if (empty($f_site) || !$t_sites->load(array('site' => $f_site))) {
            $this->addStatusMessage('Invalid Site');
            return false;
        }

        if (!$t_favorites->load(array('favorite_member' => $f_member, 'favorite_site' => $f_site))) {
            $this->addStatusMessage('You do not have this site on your favorites list.');
            return false;
        }

        return true;
    }

    public function removeSiteFromFavorites($f_site, $f_member)
    {
        $t_sites = $this->loadClass('sites');
        $t_favorites = $this->loadClass('favorites');
        $t_members = $this->loadClass('members');

        $favorite = $t_favorites->load(array('favorite_member' => $f_member, 'favorite_site' => $f_site));

        if (empty($favorite)) {
            $this->addStatusMessage('You do not have this site on your favorites list.');
            return false;
        }

        if ($t_favorites->removeFavorite($favorite['favorite'])) {
            $this->addStatusMessage('Site removed from your favorites.', 'success');
            return true;
        } else {
            $this->addStatusMessage('Unable to remove site from favorites. Please contact support.');
            return false;
        }
    }
}
