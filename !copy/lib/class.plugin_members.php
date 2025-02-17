<?php

class plugin_members extends sitemanager_page {

    public function localInitialize() {
        $this->setPluginName('members');
        $this->setPageVariable($this->getPageVariable(PAGE_MAINURL) . $this->getPluginName .'/', PAGE_METAURL);
    }

    public function localReinitialize() {
        $this->SITEMANAGER->checkPrivileges($this->member['member']);
        $this->setTemplateDirectory($this->getPluginName());
    }

    public function site_index() {

        $this->output();
    }


    public function site_disabled() {
        $this->appendTitle('Disable Account');
        if (!empty($_POST)) {
            if ($this->SITEMANAGER->confirmPassword($this->member['member'], $_POST['current_password'])) {
                if (!$this->SITEMANAGER->validateEnableAccount($this->member['member'])) {
                    $this->status_message = $this->SITEMANAGER->returnStatusMessage();
                } else {
                    if ($this->SITEMANAGER->enableAccount($this->member['member'])) {
                        $this->status_message = $this->SITEMANAGER->returnStatusMessage('success');
                        header('location: /members/');
                    } else {
                        $this->status_message = $this->SITEMANAGER->returnStatusMessage();
                    }
                }
            } else {
                $this->status_message = $this->SITEMANAGER->returnStatusMessage();
            }
        }

        $this->output();
    }


}

?>