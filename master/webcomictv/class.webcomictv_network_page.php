<?php

class webcomictv_network_page extends page {

    public function outputPrepartion() {
        $this->prependBaseFolder('/usr/local/lib/php/master/webcomictv/templates/');
        $this->prependBaseFolder('/usr/local/lib/php/master/master_templates/');
        $this->addTemplateDirectory('default');
        $this->addTemplateDirectory('site_profile');

        $this->addScriptFile('jquery-1.4.4.js');
        $this->addScriptFile('prototype.js');
        $this->addScriptFile('csspopup.js');
        $this->addScriptFile('global.js');
        $this->addScriptFile('pings.js');

        $this->addStylesheet('navigation');
        $this->addTemplateDirectory('csspopup');

        $this->addCSSPopup('member_login');
        $this->addCSSPopup('bbcode');

        if (!empty($this->status_message)) {
            $this->addCSSPopup('status_message');
            $this->addScriptEvent("popupOpen('status_message_popup');");
        }

        $this->csspopups = $this->getCSSPopups();
        $this->generateOnLogin();
        return true;
    }


    public function addCSSPopup($f_popup_name,$f_prefix='',$f_suffix='_popup') {
        $this->myCSSPopups[] = $f_prefix.$f_popup_name.$f_suffix;
    }

    public function initializeMember() {

        //if (!empty($this->member['suspend'])) { $this->logout();}
        $this->member['privileges'] = $this->SECURITYCHECKER->loadMemberPrivileges($this->member['member']);
        if ($this->SECURITYCHECKER->checkSinglePrivilege($this->member['member'], MEMBER_PRIVILEGE_ADMIN)) {
            $this->admin = true;
            $this->member['privileges']['admin'] = true;
            $this->debug_initializeUser();
            ini_set("display_errors", "1");
        }

        if ($this->SECURITYCHECKER->checkSinglePrivilege($this->member['member'], MEMBER_PRIVILEGE_CREATOR)) {
            $this->creator = true;
            $this->member['privileges']['creator'] = true;
        }
    }


    public function site_out() {
        $member = ($this->member) ? $this->member['member'] : false;
        $ad = ($_GET['ad']) ? true : false;
        $this->SITEMANAGER->exitToSite($_GET['site'], $member, $ad);
    }

    public function site_login() {
        $this->exclude_from_redirect = true;
        $this->removeCSSPopup('member_login');

        $this->appendTitle('Member Login');
        if (!empty($_POST)) {
            if ($this->SITEMANAGER->loginUser($_POST)) {
                $login_redirect = (!empty($_SESSION['REDIRECT_URL'])) ? $_SESSION['REDIRECT_URL'] : $this->page['mainurl'];
                header('location: ' . $login_redirect);
            } else {
                $this->status_message = $this->SITEMANAGER->returnStatusMessage();
            }
        }
        $this->output();
    }

    // LOGOUT
    public function site_logout() {
        $this->exclude_from_redirect = true;
        if ($this->SITEMANAGER->logoutUser()) {
            $logout_redirect = (!empty($_SESSION['REDIRECT_URL'])) ? $_SESSION['REDIRECT_URL'] : $this->page['mainurl'];
            header('location: ' . $logout_redirect);
        } else {
            $this->output();
        }
    }

    public function site_forgot() {
        $this->appendTitle('Forgot Password');
        $this->exclude_from_redirect = true;

        if (!empty($_POST)) {
            if ($this->SITEMANAGER->validateSendPasswordReminder($_POST['reset_email'])) {
                if ($this->SITEMANAGER->sendPasswordReminder($_POST['reset_email'])) {
                    $this->status_message = $this->SITEMANAGER->returnStatusMessage('success');
                    $this->success = true;
                } else {
                    $this->status_message = $this->SITEMANAGER->returnStatusMessage();
                    $this->fillForms($_POST);
                }
            } else {
                $this->status_message = $this->SITEMANAGER->returnStatusMessage();
                $this->fillForms($_POST);
            }
        }

        $this->output();
    }

    public function site_reset() {
        $this->exclude_from_redirect = true;
        $this->appendTitle('Reset Password');
        $this->code = $_GET['code'];
        if (!empty($_POST)) {
            if ($this->SITEMANAGER->validateResetPassword($_POST)) {
                if ($this->SITEMANAGER->resetPassword($_POST)) {
                    $this->status_message = $this->SITEMANAGER->returnStatusMessage('success');
                    $this->success = true;
                } else {
                    $this->status_message = $this->SITEMANAGER->returnStatusMessage();
                    $this->fillForms($_POST);
                }
            } else {
                $this->status_message = $this->SITEMANAGER->returnStatusMessage();
                $this->fillForms($_POST);
            }
        }
        $this->output();
    }

    public function site_error() {
        $this->exclude_from_redirect = true;
        $this->appendTitle('Page Not Found');

        $this->last_valid_url = $_SESSION['REDIRECT_URL'];


        $this->output();
    }

}

?>