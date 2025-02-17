<?php
require_once ('/usr/local/lib/php/master/class.flexy_handler.php');

class my_flexy_handler extends flexy_handler {

    protected function initialize() {
        $this->addDirectoryToLibrary('../lib/');
        $this->addDirectoryToLibrary('/usr/local/lib/php/');
        $this->addDirectoryToLibrary('/usr/local/lib/php/master/');

        $this->addPEARFiles('HTML/Template/Flexy.php');
        $this->addPEARFiles('HTML/Template/Flexy/Element.php');
        $this->addPEARFiles('HTML/BBCodeParser.php');
        $this->addPEARFiles('Mail/mimeDecode.php');
        $this->addPEARFiles('Mail.php');
    }

    protected function finalize() {
        $this->displayPagePlugin();
    }

    public function setMetaSite($f_metasite=false) {
        ($f_metasite) ? $this->addDirectoryToLibrary('/usr/local/lib/php/master/' . $f_metasite . '/') : $this->removeFromDirectoryToLibrary('/usr/local/lib/php/master/' . $f_metasite . '/');
        $this->metaSite = $f_metasite;
    }

}

?>