<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class flexy_page {

    private $baseFolders = array('../templates/');
    private $pageMethodPrefix = 'pg';
    private $templateDirectories = array('default');
    private $pageTemplateName;
    private $pageFunctionName;
    private $pluginName;
    private $page_stylesheets;
    private $page_scriptfiles;
    private $page_script_events;
    private $page_titles;
    private $flexy_page_data;
    private $flexy_elements_data;
    private $outputPreperationHasRun = false;
    private $forceOutputPreperation = false;
    // OLD VARIABLES
    // Array: Strings: Template Directories in order of which to check first. Lower number get checked first.
    public $tpldirs = array('default');
    // Array: Strings: Stylesheets filesnames, without '.css'
    public $stylesheets;
    // Array: Javascript files stored as Array: "('src' => $f_script_filename, $f_type);"
    public $scriptfiles;
    // String: Holds the name of template to display
    public $pg;
    // Array: Holds information about page (used to display titles, etc).
    public $page;




    public function __construct($f_pageClass=false) {

        DEFINE('PAGE_PROPERTY_NAME', 'page');
        DEFINE('PAGE_MAINURL', 'mainurl');
        DEFINE('PAGE_METAURL', 'metaurl');
        DEFINE('PAGE_SITENAME', 'sitename');
        DEFINE('PAGE_TITLE', 'title');
        DEFINE('PAGE_KEYWORDS', 'keywords');
        DEFINE('PAGE_DESCRIPTION', 'description');
        DEFINE('PAGE_UPDATED', 'updated');
        DEFINE('PAGE_DOMAIN', 'domain');
        DEFINE('PAGE_SUBDOMAIN', 'subdomain');

        DEFINE('DEFAULT_PLUGIN_NAME', 'default_plugin');

        DEFINE('DEBUG_SESSION_NAME', 'debug_out_ip');

        if (!empty($f_pageClass)) {
            $this->setPluginName($f_pageClass);
        }
        if (method_exists($this, 'localInitialize')) {
            $this->localInitialize();
        }
        if (method_exists($this, 'globalInitialize')) {
            $this->globalInitialize();
        }
        if (method_exists($this, 'localReinitialize')) {
            $this->localReinitialize();
        }
    }

    public function outputPrepartion() {
        return true;
    }

    public function initializeTemplatePage($pg) {
        $this->pageTemplateName = $pg;
        $this->pageFunctionName = $this->formatPageFunctionName($pg);
        if (!empty($this->outer_page)) {
            $this->pageTemplateName = $this->outer_page;
        }
    }

    protected function setInternalPage($f_outer_page, $f_inner_page, $f_ignore_title=true) {
        $this->outer_page = $f_outer_page;
            $this->pageTemplateName = $this->outer_page;
        $this->display_page = $f_inner_page;
        $this->ignoreTitle = $f_ignore_title;
    }

    protected function findTemplate($f_template_name, $f_directories) {
        $foundTemplate = false;
        for ($a = 0; $a < count($this->baseFolders) && !$foundTemplate; $a++) {
            $baseFolder = $this->baseFolders[$a];
            $tempDirectories = $f_directories;
            while (!empty($tempDirectories) && $directory = $baseFolder . array_pop($tempDirectories)) {
                if (file_exists($directory . '/' . $f_template_name . '.tpl')) {
                    return $directory;
                }
            }
        }
        return false;
    }

    public function output($f_name='main', $f_tpldir=false) {
        // COVER ALL PREVIOUS SITES THAT ACCESS tpldirs DIRECTLY
        //$this->templateDirectories = array_merge($this->tpldirs, $this->templateDirectories);
        //$this->templateDirectories = array_merge($this->tpldirs, $this->templateDirectories);
        $this->pg = $this->pageTemplateName;
        $this->runOutputPreperation();
        if ($f_tpldir) {
            $templateDirectory = $f_tpldir;
        } else {
            if (!($templateDirectory = $this->findTemplate($f_name, $this->templateDirectories))) {
                return false;
            }
        }
        $templateObject = new HTML_Template_Flexy(array('templateDir' => $templateDirectory, 'compileDir' => '/tmp/flexy_compiled_templates/'));
        $templateObject->compile($f_name . '.tpl');
        // SUPPORT OLD PAGES
        $this->supportOldPages();

        if (isset($this->flexy_elements_data)) {
            $templateObject->outputObject($this, $this->flexy_elements_data);
        } else {
            $templateObject->outputObject($this);
        }
    }

    public function runOutputPreperation() {

        if (!$this->outputPreperationHasRun || $this->forceOutputPreperation) {
            $this->checkDefaultTitles();
            if ($this->outputPrepartion()) {
                $this->outputPreperationHasRun = true;
                $this->forceOutputPreperation = false;
            }
        }
        return true;
    }

    public function checkDefaultTitles() {
        if (!method_exists($this, $this->pageFunctionName)) {
            $pluginName = ($this->pluginName) ? $this->pluginName : DEFAULT_PLUGIN_NAME;
            if ($this->getPageTitles($pluginName, $this->pageTemplateName)) {
                $this->appendTitle($this->getPageTitles($pluginName, $this->pageTemplateName));
            } else {
                if (!$this->ignoreTitle) {
                    $title = preg_replace('![^a-z0-9]+!i', ' ', $this->pageTemplateName);
                    $this->appendTitle(ucwords($title));
                }
            }
        }
    }

    public function formatPageFunctionName($f_page_template_name) {
        return $this->pageMethodPrefix . '_' . str_replace('-', '_', $f_page_template_name);
    }

    public function display($pg) {
        $this->initializeTemplatePage($pg);
        if (method_exists($this, $this->pageFunctionName)) {
            call_user_func(array($this, $this->pageFunctionName));
        } else {
            return $this->output();
        }
    }

    public function configurePageData($f_sitename, $f_title, $f_mainurl, $f_keywords=false, $f_description=false) {
        $this->flexy_page_data[PAGE_SITENAME] = $f_sitename;
        $this->flexy_page_data[PAGE_TITLE] = $f_title;
        $this->flexy_page_data[PAGE_MAINURL] = $f_mainurl;
        $this->flexy_page_data[PAGE_KEYWORDS] = ($f_description) ? $f_keywords : $f_sitename;
        $this->flexy_page_data[PAGE_DESCRIPTION] = ($f_description) ? $f_description : $f_sitename . ' | ' . $f_title;
    }

    public function getPageData() {
        return $this->flexy_page_data;
    }

    public function setPageVariable($f_value, $f_page_variable) {
        $this->flexy_page_data[$f_page_variable] = $f_value;
    }

    public function setPageVariables($fa_values) {
        $this->flexy_page_data = $fa_values;
    }

    public function getPageVariable($f_page_variable=false) {
        return ($f_page_variable) ? $this->flexy_page_data[$f_page_variable] : $this->flexy_page_data;
    }

    public function pageValue($f_page_variable=false) {
        return ($f_page_variable) ? $this->flexy_page_data[$f_page_variable] : $this->flexy_page_data;
    }

    public function configurePlugin($f_plugin_name, $f_override_template_at_position=false, $f_plugin_main_url=false, $f_property_name=false) {

        $f_property_name = (!$f_property_name) ? PAGE_PROPERTY_NAME : $f_property_name;

        $mainurl = ($f_plugin_main_url) ? $f_plugin_main_url : $this->$f_property_name[PAGE_MAINURL];

        $this->setPluginName($f_plugin_name);

        $this->$f_property_name[PAGE_METAURL] = $mainurl . $f_plugin_name . '/';
        $this->addTemplateDirectory($f_plugin_name, $f_override_template_at_position);
        $this->addStylesheet($f_plugin_name, $f_override_template_at_position);
        return true;
    }

    public function setTemplateDirectory($f_template_directory) {
        unset($this->templateDirectories);
        unset($this->stylesheets);
        $this->addTemplateDirectory($f_template_directory);
        return true;
    }

    public function addTemplateDirectory($f_template_directory, $f_position=false, $f_overwrite=true, $f_stylesheet=true) {
        $spliceReplace = ($f_overwrite) ? 1 : 0;
        if ($f_stylesheet) {
            $this->addStylesheet($f_template_directory, $f_position, $f_overwrite);
        }
        return ($f_position !== false) ? array_splice($this->templateDirectories, $f_position, $spliceReplace, $f_template_directory) : $this->templateDirectories[] = $f_template_directory;
    }

    public function removeTemplateDirectory($f_template_directory) {
        while (($templatePosition = array_search($f_template_directory, $this->templateDirectories)) !== false) {
            unset($this->templateDirectories[$templatePosition]);
        }
        return true;
    }

    public function getTemplateDirectories($f_position=false) {
        return ($f_position) ? $this->templateDirectories[$f_position] : $this->templateDirectories;
    }

    public function addBaseFolder($f_base_folder, $f_position=false) {
        if ($f_position !== false) {
            array_splice($this->baseFolders, $f_position, 0, $f_base_folder);
        } else {
            $this->baseFolders[] = $f_base_folder;
        }
        return true;
    }

    public function prependBaseFolder($f_base_folder) {
        array_unshift($this->baseFolders, $f_base_folder);
        return true;
    }

    public function removeBaseFolder($f_base_folder, $f_position=false) {
        if ($f_position) {
            unset($this->baseFolders[$f_position]);
        }
        while (($position = array_search($f_base_folder, $this->baseFolders)) !== false) {
            unset($this->baseFolders[$position]);
            return true;
        }
        return false;
    }

    public function getBaseFolders($f_position=false) {
        return ($f_position) ? $this->baseFolders[$f_position] : $this->baseFolders;
    }

    public function addScriptFile($f_script_filename, $f_type='text/javascript', $f_position=false) {
        $file = array('src' => $f_script_filename, $f_type);
        return ($f_position) ? $this->scriptfiles[$f_position] = $file : $this->scriptfiles[] = $file;
    }

    public function removeScriptFile($f_script_filename) {
        unset($this->scriptfiles[$f_script_filename]);
        return true;
    }

    public function setScriptFile($f_script_filename, $f_type='text/javascript') {
        $file = array('src' => $f_script_filename, $f_type);
        unset($this->scriptfiles);
        return $this->scriptfiles[0] = $file;
    }

    public function setScriptFiles($f_position=false) {
        return ($f_position) ? $this->scriptfiles[$f_position] : $this->scriptfiles;
    }

    public function addStylesheet($f_stylesheet, $f_position=false) {
        return ($f_position) ? $this->stylesheets[$f_position] = $f_stylesheet : $this->stylesheets[] = $f_stylesheet;
    }

    public function removeStylesheet($f_stylesheet) {
        while (($pos = array_search($f_stylesheet, $this->stylesheets)) !== false) {
            unset($this->stylesheets[$pos]);
        }
        return true;
    }

    public function setStylesheet($f_stylesheet) {
        unset($this->stylesheets);
        $this->stylesheets[0] = $f_stylesheet;
    }

    public function getStylesheets($f_position=false) {
        return ($f_position) ? $this->stylesheets[$f_position] : $this->stylesheets;
    }

    public function addScriptEvent($f_scriptevent, $f_position=false) {
        return ($f_position) ? $this->page_script_events[$f_position] = $f_scriptevent : $this->page_script_events[] = $f_scriptevent;
    }

    public function outputScriptEvents() {
        if (!empty($this->page_script_events)) {
            foreach ($this->page_script_events as $event) {
                $string .= "<script>Event.observe( window, 'load', function() {" . $event . "} );</script>";
            }
        }
        echo $string;
    }

    public function getScriptEvents($f_position=false) {
        return ($f_position) ? $this->page_script_events[$f_position] : $this->page_script_events;
    }

    // PAGE VALUE FUNCTIONS

    public function appendPageValue($f_value, $f_array_id, $f_seperator='|') {
        if (empty($f_array_id)) {
            return false;
        }
        if (is_array($f_value)) {
            $f_value = implode(',', $f_value);
        }
        $this->flexy_page_data[$f_array_id] = (empty($this->flexy_page_data[$f_array_id])) ? $f_value : $f_value . ' ' . $f_seperator . ' ' . $this->flexy_page_data[$f_array_id];
        return $this->flexy_page_data[$f_array_id];
    }

    public function appendTitle($f_title, $f_seperator='|', $f_array_id=false) {
        $f_array_id = ($f_array_id) ? $f_array_id : PAGE_TITLE;
        return $this->appendPageValue($f_title, $f_array_id, $f_seperator, $f_property_name);
    }

    public function appendDescription($f_description, $f_seperator='-', $f_array_id=false) {
        $f_array_id = ($f_array_id) ? $f_array_id : PAGE_DESCRIPTION;
        return $this->appendPageValue($f_description, $f_array_id, $f_seperator, $f_property_name);
    }

    public function addKeywords($f_keywords, $f_seperator=',', $f_array_id=false) {
        $f_array_id = ($f_array_id) ? $f_array_id : PAGE_KEYWORDS;
        return $this->appendPageValue($f_keywords, $f_array_id, $f_seperator, $f_property_name);
    }

    public function addPageTitle($f_page_name, $f_page_title, $f_page_plugin=false) {

        $pluginName = ($f_page_plugin) ? $f_page_plugin : DEFAULT_PLUGIN_NAME;

        $this->page_titles[$pluginName][$f_page_name] = $f_page_title;

        return $this->page_titles[$pluginName][$f_page_name];
    }

    public function getPageTitles($f_page_plugin=false, $f_page_name=false) {
        if ($f_page_plugin) {
            return ($f_page_name) ? $this->page_titles[$f_page_plugin][$f_page_name] : $this->page_titles[$f_page_plugin];
        } else {
            return $this->page_titles;
        }
    }

    // Form ELEMENT METHODS

    public function initilizeFormElement($f_element_name) {
        if (empty($this->flexy_elements_data[$f_element_name])) {
            $this->flexy_elements_data[$f_element_name] = new HTML_Template_Flexy_Element;
        }
        return $this->flexy_elements_data;
    }

    public function unsetFormElement($f_element_name) {
        unset($this->flexy_elements_data[$f_element_name]);
    }

    public function setFormElementOptions($f_element_name, $fa_options) {
        $this->initilizeFormElement($f_element_name);
        $this->flexy_elements_data[$f_element_name]->setOptions($fa_options);
    }

    public function setFormElementValue($f_element_name, $f_value) {
        $this->initilizeFormElement($f_element_name);
        $this->flexy_elements_data[$f_element_name]->setValue($f_value);
    }

    public function setFormElementAttributes($f_element_name, $fa_attributes) {
        $this->initilizeFormElement($f_element_name);
        $this->flexy_elements_data[$f_element_name]->setAttributes($fa_attributes);
    }

    public function fillForms($fa_form_array) {
        if (empty($fa_form_array) || !is_array($fa_form_array)) {
            return false;
        }
        foreach ($fa_form_array as $elementName => $elementValue) {
            $this->setFormElementValue($elementName, $elementValue);
            if (is_array($elementValue)) {
                $this->setFormElementValue($elementName . '[]', $elementValue);
            }
        }
        return $this->flexy_elements_data;
    }

    /// DEBUG FUNCTIONS

    public function debug_initializeUser($f_session_ip_name=false) {
        $debug_session_name = ($f_session_ip_name) ? $f_session_ip_name : DEBUG_SESSION_NAME;

        return $_SESSION[$debug_session_name] = $_SERVER['REMOTE_ADDR'];
    }

    public function debug_pageDisplayOutput($f_variable_for_output, $f_explicit_page, $f_pre_tags=true, $f_show_to_ip=false) {

        if ($this->pageTemplateName == $f_explicit_page) {
            return $this->debug_displayOutput($f_variable_for_output, $f_pre_tags, $f_show_to_ip);
        }
        return false;
    }

    public function debug_displayOutput($f_variable_for_output, $f_pre_tags=true, $f_show_to_ip=false) {
        $outPut = $this->debug_getOutput($f_variable_for_output, $f_pre_tags, $f_show_to_ip);
        if ($outPut) {
            echo $outPut;
            return $outOut;
        } else {
            return false;
        }
    }

    public function debug_getOutput($f_variable_for_output, $f_pre_tags=true, $f_show_to_ip=false) {
        $showToIPFinal = ($f_show_to_ip) ? $f_show_to_ip : $_SESSION[DEBUG_SESSION_NAME];
        if (!empty($showToIPFinal) && $_SERVER['REMOTE_ADDR'] == $showToIPFinal) {
            $outPut = '';
            if ($f_pre_tags) {
                $outPut .= '<pre>';
            }
            if (is_array($f_variable_for_output) || $f_variable_for_output === false) {
                $outPut .= var_export($f_variable_for_output, true);
            } else {
                $outPut .= $f_variable_for_output;
            }
            if ($f_pre_tags) {
                $outPut .= '</pre>';
            }

            return $outPut;
        }
        return false;
    }

    // FLEXY RETURN FUNCTIONS


    public function flexy_encodeString($f_string, $f_replace='-', $f_lower=true) {
        $f_string = preg_replace("[\-$]", '', preg_replace("[\W+]", $f_replace, $f_string));
        if ($f_lower) {
            return strtolower($f_string);
        }
        return $f_string;
    }

    public function flexy_dateFormat($f_format, $f_date=false) {
        return ($f_date) ? date($f_format, strtotime($f_date)) : date($f_format);
    }

    public function flexy_numberFormat($f_number, $f_decimal_places=0, $f_decimal_seperator='.', $f_thounsands_seperator=',') {
        return number_format($f_number, $f_decimal_places, $f_decimal_seperator, $f_thounsands_seperator);
    }

    public function flexy_implodeArray($f_pieces, $f_glue=',') {
        return implode($f_glue, $f_pieces);
    }

    public function flexy_rowMod($f_integer, $f_mod=2, $f_plus=1, $f_string="row") {
        return $f_string . (($f_integer % $f_mod) + $f_plus);
    }

    public function flexy_storeValue($f_variable_name, $f_new_value, $f_return = false) {
        $this->$f_variable_name = $f_new_value;
        if ($f_return) {
            return $this->$f_variable_name;
        }
    }

    public function flexy_addValue($f_variable_name, $f_new_value, $f_return = false) {
        $this->$f_variable_name += $f_new_value;
        return ($f_return) ? $this->$f_variable_name : true;
    }

    public function flexy_subtractValue($f_variable_name, $f_new_value, $f_return = false) {
        $this->$f_variable_name -= $f_new_value;
        return ($f_return) ? $this->$f_variable_name : true;
    }

    public function flexy_appendValue($f_variable_name, $f_new_value, $f_return = false) {
        $this->$f_variable_name .= $value;
        return ($f_return) ? $this->$f_variable_name : true;
    }

    public function flexy_truncateString($f_string_to_truncate, $f_cut_to_length, $f_add_to_end='', $f_start_position=0) {
        if (strlen(strip_tags($f_string_to_truncate)) > $f_cut_to_length) {
            $f_string_to_truncate = strip_tags($f_string_to_truncate);
            return substr($f_string_to_truncate, $f_start_position, $f_cut_to_length) . $f_add_to_end;
        } else {
            return $f_string_to_truncate;
        }
    }

    public function flexy_mathAdd($f_number_1, $f_number_2=1) {
        return $f_number_1 + $f_number_2;
    }

    public function flexy_mathMultiply($f_number_1, $f_number_2=1) {
        return $f_number_1 * $f_number_2;
    }

    public function flexy_mathSubtract($f_number_1, $f_number_2=1) {
        return $f_number_1 - $f_number_2;
    }

    public function flexy_mathDivide($f_number_1, $f_number_2=1) {
        if ($f_number_2 == 0) {
            return false;
        }
        return $f_number_1 / $f_number_2;
    }

    public function flexy_mathRound($f_number, $f_decimal_places=2) {
        return round($f_number, $f_decimal_places);
    }

    public function flexy_mathMod($f_number, $f_mod_by=2, $f_plus=0) {
        if (($f_number + $f_plus) % $f_mod_by) {
            return false;
        } else {
            return true;
        }
    }

    public function flexy_checkMod($f_number, $f_mod_by=2, $f_plus=0) {
        if (($f_number + $f_plus) % $f_mod_by) {
            return false;
        } else {
            return true;
        }
    }

    public function flexy_compareLessThan($f_number_1, $f_number_2, $f_and_equal = false) {
        if ($f_and_equal) {
            return $f_number_1 <= $f_number_2;
        } else {
            return $f_number_1 < $f_number_2;
        }
    }

    public function flexy_compareGreaterThan($f_number_1, $f_number_2, $f_and_equal = false) {
        if ($f_and_equal) {
            return $f_number_1 >= $f_number_2;
        } else {
            return $f_number_1 > $f_number_2;
        }
    }

    public function flexy_text2html($f_string, $f_autolink=false, $f_paragraph=true) {
        $f_string = str_replace('\r\n\r\n', '</p><p>', $f_string);
        $f_string = nl2br($f_string);
        if ($f_autolink) {
            $f_string = $this->flexy_hyperlink($f_string);
        }
        if ($f_paragraph) {
            return '<p>' . $f_string . '</p>';
        } else {
            return $f_string;
        }
    }

    public function flexy_text2html2($f_string, $f_autolink=false, $f_paragraph=true) {
        $pattern = '/\n\n/';
        $f_string = preg_replace($pattern, '</p><p>', $f_string);
        $f_string = nl2br($f_string);
        if ($f_autolink) {
            $f_string = $this->flexy_hyperlink($f_string);
        }
        if ($f_paragraph) {
            return '<p>' . $f_string . '</p>';
        } else {
            return $f_string;
        }
    }

    function flexy_hyperlink($f_string) {
        // match protocol://address/path/file.extension?some=variable&another=asf%
        $f_string = preg_replace("/([a-zA-Z]+:\/\/[a-z0-9\_\.\-]+[a-z]{2,6}[a-zA-Z0-9\/\*\-\?\_\&\%\=\,\.]+)/", "<a href=\"$1\">$1</a>", $f_string);
        // match www.something.domain/path/file.extension?some=variable&another=asf%
        // $f_string = preg_replace("/[^a-z]+[^:\/\/](www\.[^\.]+[\w][\.|\/][a-zA-Z0-9\/\*\-\?\&\%\=\,\.]+)/"," <a href=\"http://$1\" target=\"_blank\">$1</a>", $f_string);
        // match name@address + ?subject etc...
        //$f_string = preg_replace("/([\s|\,\>])([a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]*\@[a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]{2,6})([A-Za-z0-9\!\?\@\#\$\%\^\&\*\(\)\_\-\=\+]*)([\s|\.|\,\<])/i","$1<a href=\"mailto:$2$3\">$2</a>$4",$f_string);
        return $f_string;
    }

    public function flexy_compareValues($f_value_1, $f_value_2, $f_not_equal=false, $f_is_exact=false) {
        if (!$f_is_exact) {
            if (!$f_not_equal) {
                return ($f_value_1 == $f_value_2);
            } else {
                return ($f_value_1 != $f_value_2);
            }
        } else {
            if (!$f_not_equal) {
                return ($f_value_1 === $f_value_2);
            } else {
                return ($f_value_1 !== $f_value_2);
            }
        }
    }

    public function flexy_convertToBBCode($f_string) {
        $parser = new HTML_BBCodeParser();
        $parser->setText(strip_tags($f_string));
        $parser->addfilters('links,lists,basic,forums,extended');
        $parser->parse();
        return $this->flexy_text2html2($parser->getParsed());
    }

    private function supportOldPages() {
        if (!empty($this->flexy_page_data) && !empty($this->page)) {
            $this->page = array_merge($this->page, $this->flexy_page_data);
            $this->flexy_page_data = $this->page;
        }
        if (!empty($this->page_stylesheets)) {
            $this->stylesheets = array_merge($this->stylesheets, $this->page_stylesheets);
            $this->page_stylesheets = $this->stylesheets;
        }
        if (!empty($this->page_scriptfiles)) {
            $this->scriptfiles = array_merge($this->scriptfiles, $this->page_scriptfiles);
            $this->page_scriptfiles = $this->scriptfiles;
        }
        if (!empty($this->elements) && !empty($this->flexy_elements_data)) {
            $this->flexy_elements_data = array_merge($this->flexy_elements_data, $this->elements);
            $this->elements = $this->flexy_elements_data;
        }
        return true;
    }

    public function stest($f_number) {
        return($f_number > 1) ? 's' : '';
    }

    public function outputIcon($f_icon_filename, $f_icon_title=false, $f_icon_id=false, $f_align='absmiddle', $f_icon_alt=false, $f_icon_width=16, $f_icon_height=16) {
        $f_icon_title = ($f_icon_title) ? $f_icon_title : str_replace('_', ' ', strtoupper($f_icon_filename));
        $f_icon_alt = ($f_icon_alt) ? $f_icon_alt : $f_icon_title;
        $icon_string = '<img src="/images/icons/' . $f_icon_filename . '.png" ';
        if ($f_icon_id) {
            $icon_string .= ' id="' . $f_icon_id . '" ';
        }

        $icon_string .= ' title="' . $f_icon_title . '" alt="' . $f_icon_alt . '" align="' . $f_align . '" width="' . $f_icon_width . '" height="' . $f_icon_height . '" border="0" />';
        return $icon_string;
    }

    public function outputAjaxLoader($f_id, $f_filename='ajax-loader.gif') {
        $loader_string = '<img src="/images/' . $f_filename . '" id="ajax_loader_' . $f_id . '" style="display: none;"  align="absmiddle"  width="16px" height="16px" title="ajax loading" alt="loading" />';
        return $loader_string;
    }

    public function outputLinkButton($f_button_id, $f_active_text, $f_inactive_text, $f_initial_value=1, $f_onclick=false, $f_class_name_on='toggle_on_button', $f_class_name_off='toggle_off_button', $f_default_class='button_link', $f_on_value=1, $f_off_value=0) {
        $f_initial_class = ($f_initial_value == $f_on_value) ? $f_class_name_on : $f_class_name_off;
        $f_initial_text = ($f_initial_value == $f_on_value) ? $f_active_text : $f_inactive_text;
        $f_initial_value = ($f_initial_value == $f_on_value) ? $f_on_value : $f_off_value;
        $onClick = ($f_onclick) ? $f_onclick : $onClick = "toggleButton('$f_button_id','$f_active_text','$f_inactive_text','$f_class_name_on','$f_class_name_off','$f_default_class');";
        $button_link_string = '<a onclick="' . $onClick . '" id="button_' . $f_button_id . '" class="' . $f_default_class . ' ' . $f_initial_class . '">' . $f_initial_text . '</a>';
        $button_input_string = '<input type="hidden" id="' . $f_button_id . '" name="' . $f_button_id . '" value="' . $f_initial_value . '"/>';
        $final_string = $button_link_string . $button_input_string;
        return $final_string;
    }

    // ******************************* SETTERS AND GETTERS *******************************



    public function setPageTemplateName($f_page_template_name) {
        $this->pageTemplateName = $f_page_template_name;
    }

    public function getPageTemplateName() {
        return $this->pageTemplateName;
    }

    public function setPageFunctionName($f_page_function_name) {
        $this->pageFunctionName = $f_page_function_name;
    }

    public function getPageFunctionName() {
        return $this->pageFunctionName;
    }

    public function setMethodPrefix($f_new_method_prefix) {
        $this->pageMethodPrefix = $f_new_method_prefix;
    }

    public function getMethodPrefix() {
        return $this->pageMethodPrefix;
    }

    public function setPluginName($f_plugin_name) {
        $this->site = $f_plugin_name;
        $this->pluginName = $f_plugin_name;
        return $f_plugin_name;
    }

    public function getPluginName() {
        return $this->pluginName;
    }

    public function setMetaSite($f_metasite) {
        $this->metasite = $f_metasite;
    }

    public function getMetaSite() {
        return $this->metasite;
    }

}

?>
