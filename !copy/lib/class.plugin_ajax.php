<?php

class plugin_ajax extends sitemanager_page {

    public function localInitialize() {
        $this->setPluginName('ajax');
        $this->setPageVariable($this->getPageVariable(PAGE_MAINURL) . $this->getPluginName .'/', PAGE_METAURL);
    }

    public function localReinitialize() {
        $this->setTemplateDirectory($this->getPluginName());
    }

}

?>