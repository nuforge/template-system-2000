<?php

class plugin_ajax extends sitemanager_page {

    public function localInitialize() {
        $this->setPluginName('ajax');
        $this->setPageVariable($this->getPageVariable(PAGE_MAINURL) . $this->getPluginName .'/', PAGE_METAURL);
    }

    public function localReinitialize() {
        $this->setTemplateDirectory($this->getPluginName());
    }

    
    public function site_moveconversation() {
        if (empty($this->member['member'])) {
            echo 'Invalid Member';
            return false;
        }
        $result = $this->PRIVATE_MESSAGE_MANAGER->toggleConversationFolder($conversation['conversation'], $_GET['folder']);
        if ($result) {
            echo $result;
            return true;
        } else {
            echo $this->PRIVATE_MESSAGE_MANAGER->returnStatusMessage();
            return false;
        }
    }
    
}

?>