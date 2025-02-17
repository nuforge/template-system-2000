<?php

class _site extends sitemanager_page {

    public function localInitialize() {
        $this->site = get_class();
    }

    public function localReinitialize() {

    }

    public function site_index() {
        $this->appendTitle('Welcome');

        $this->output();
    }

    public function site_contact() {
        $this->appendTitle('Contact Us');
        $this->addCSSPopup('contact_confirm');
        if (!empty($_POST)) {
            if ($this->SITEMANAGER->validateContactUs($_POST)) {
                $this->SITEMANAGER->sendContactUs($_POST);
            }
            $this->status_message = $this->SITEMANAGER->returnStatusMessage();
            if ($this->SITEMANAGER->checkStatusMessage()) {
                $this->fillforms($_POST);
            }
        }
        $this->output();
    }

    public function site_forgot() {
        $this->appendTitle('Forgot Password');

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

    public function site_login() {
        $this->appendTitle('Member Login');

        if (!empty($_POST)) {
            if ($this->SITEMANAGER->loginUser($_POST)) {
                if (!empty($_SESSION['redir_url'])) {
                    header('location: ' . $this->page['mainurl'] . $_SESSION['redir_url']);
                } else {
                    header('location: ' . $this->page['mainurl'] . 'members/index.html');
                }
            } else {
                $this->status_message = $this->SITEMANAGER->returnStatusMessage();
            }
        }
        $this->output();
    }

    // LOGOUT
    public function site_logout() {
        if ($this->SITEMANAGER->logoutUser()) {
            unset($this->member);
            header('location: ' . $this->page['mainurl']);
        }
    }

    public function site_reset() {
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

}

?>