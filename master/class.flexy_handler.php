<?php

class flexy_handler {

    protected $classPrefixes = array('class');
    protected $classExtensions = array('php');
    protected $siteName;
    protected $metaSite;
    protected $directoryLibrary;
    protected $pluginName;
    protected $pageTemplate;

    public function __construct($f_sitename=false, $f_plugin=false, $f_template=false, $f_metasite=false) {
        spl_autoload_register(array($this, 'autoloadClass'));
        $this->initialize();
        if($f_plugin) {$this->setPlugin($f_plugin);}
        if($f_template) {$this->setPageTemplate($f_template);}
        DEFINE ('FLEXY_HANDLER_DEFAULT_PAGE','index');
        $this->setSiteName($f_sitename);
        ($f_metasite) ? $this->setMetaSite($f_metasite) : $this->setMetaSite($f_sitename);
        $this->finalize();
    }

    protected function initialize() {
        return true;
    }

    protected function finalize() {
        return true;
    }
    protected function autoloadClass($f_class_name) {
        $tempLibrary = $this->directoryLibrary;

        $fileLocation = $this->findClassInLibrary($f_class_name);
        if ($fileLocation) {
            require_once $fileLocation;
        }
    }
    

    public function displayPagePlugin($f_plugin=false, $f_template=false) {
        if($f_plugin) {$this->setPlugin($f_plugin);}
        if($f_template) {$this->setPageTempalte($f_template);}
        $pluginName = ($this->getPlugin()) ? $this->getPlugin() : $this->getSiteName();
        if (isset($pluginName) && $this->findClassInLibrary($pluginName)) {
            $pluginObject = new $pluginName;
            $pageTemplate = ($this->getPageTemplate()) ? $this->getPageTemplate() : FLEXY_HANDLER_DEFAULT_PAGE;
            if ($pluginObject->display($pageTemplate) === false) {
                $pluginObject->display(FLEXY_HANDLER_DEFAULT_PAGE);
            }
        }
    }


    public function displayErrors($f_display=true) {
        return ($f_display) ? ini_set("display_errors", "1") : ini_set("display_errors", "0");
    }

    public function addPEARFiles($f_pear_file) {
        require_once $f_pear_file;
    }

    public function addClassPrefix($f_new_class_prefix, $f_position=false, $f_overwrite=false) {
        $spliceReplace = ($f_overwrite)  ? 1: 0;
        ($f_position) ? array_splice($this->classPrefixes, $f_position, $spliceReplace, $f_new_class_prefix) : $this->classPrefixes[] = $f_new_class_prefix;
        return true;
    }

    public function addClassExtensions($f_new_class_extension, $f_position=false, $f_overwrite=false) {
        $spliceReplace = ($f_overwrite)  ? 1: 0;
        ($f_position) ? array_splice($this->classExtensions, $f_position, $spliceReplace, $f_new_class_extension) : $this->classExtensions[] = $f_new_class_extension;
        return true;
    }

    public function addDirectoryToLibrary($f_new_directory, $f_position=false, $f_overwrite=false) {
        $spliceReplace = ($f_overwrite)  ? 1: 0;
        ($f_position) ? array_splice($this->directoryLibrary, $f_position, $spliceReplace, $f_new_directory) : $this->directoryLibrary[] = $f_new_directory;
        return true;
    }

    public function removeFromDirectoryToLibrary($f_directory_name) {
        $directoryPosition = array_search($f_directory, $this->directoryLibrary);
        if ($directoryPosition !== false) {
            return array_splice($this->directoryLibrary, $directoryPosition, 1);
        }
        return false;
    }

    public function findClassInLibrary($f_class_name, $f_return_full_path=true, $f_lowercase_filenames=true) {
        $classFileName = ($f_lowercase_filenames) ? strtolower($f_class_name) : $f_class_name;

        foreach ($this->getDirectoryLibrary() as $checkDirectory) {
            foreach ($this->getClassPrefixes() as $checkPrefix) {
                foreach ($this->getClassExtensions() as $checkExtension) {
                    $fullFilename = $checkPrefix . '.' . $classFileName . '.' . $checkExtension;
                    if (@file_exists($checkDirectory . $fullFilename)) {
                        return ($f_return_full_path) ? $checkDirectory . $fullFilename : $checkDirectory;
                    }
                }
            }
        }
        return false;
    }


    public function setPlugin ($f_new_plugin_name) {
        $this->pluginName = ($f_new_plugin_name) ? $f_new_plugin_name : false;
    }
    public function getPlugin () {
        return $this->pluginName;
    }

    public function setPageTemplate ($f_new_page_template) {
        $this->pageTemplate = ($f_new_page_template) ? $f_new_page_template : false;
    }
    public function getPageTemplate () {
        return $this->pageTemplate;
    }

    public function getClassPrefixes() {
        return $this->classPrefixes;
    }

    public function getClassExtensions() {
        return $this->classExtensions;
    }

    public function getDirectoryLibrary() {
        return $this->directoryLibrary;
    }

    public function setMetaSite($f_metasite=false) {
        $this->metaSite = $f_metasite;
    }

    public function getMetaSite() {
        return $this->metaSite;
    }
    public function setSiteName($f_sitename=false) {
        $this->siteName = $f_sitename;
    }

    public function getSiteName() {
        return $this->siteName;
    }

}

?>