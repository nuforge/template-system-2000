<?php

class sitemanager_page extends page {

    public function globalInitialize() {

        $this->SITEMANAGER = new sitemanager;
        $this->SITEMANAGER->setStatusMessageOutput(MESSAGE_OUTPUT_TYPE_BOOTSTRAP);
        //$this->member = $this->SITEMANAGER->autoLoginMember();
        //$this->SECURITYCHECKER = new securityChecker();

        $this->setMethodPrefix('site');
        $this->setMetaSite('_SITE');
        $this->setTemplateDirectory($this->getMetaSite());
        
        $this->setPageVariable('_SITENAME', PAGE_SITENAME);
        $this->setPageVariable('PAGE TITLE', PAGE_TITLE);
        $this->setPageVariable('www', PAGE_SUBDOMAIN);
        $this->setPageVariable($this->getMetaSite() . '.com', PAGE_DOMAIN);
        $this->setPageVariable('http://' . $this->getPageVariable(PAGE_SUBDOMAIN) . '.' . $this->getPageVariable(PAGE_DOMAIN) . '/', PAGE_MAINURL);
        $this->setPageVariable('', PAGE_KEYWORDS);
        $this->setPageVariable('', PAGE_DESCRIPTION);
        
        $this->SITEMANAGER->setPageData($this->getPageData());

        //$this->debug_initializeUser();

        /*
        if (!empty($this->member['member'])) {
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
        }*/


        //$this->addTemplateDirectory('csspopup');
        
        //$this->addStylesheet('csspopup');
        //$this->addStylesheet('navigation');

        //$this->addScriptfile('jquery-1.4.4.js');
        //$this->addScriptfile('prototype.js');
        //$this->addScriptfile('csspopup.js');
        //$this->addScriptfile('global.js');
        //$this->addScriptfile('nav.js');
        //$this->addScriptfile('pings.js');
        //$this->addCSSPopup('member_login');
        //$this->addCSSPopup('bbcode');
    }


    public function outputPrepartion($f_force_run = false) {
        if ($this->outputPrepRan && !$f_force_run) {
            return false;
        }
        $this->outputPrepRan = true;
        $this->statusMessage = $this->SITEMANAGER->checkHasStatusMessage();
        if(!empty($this->status_message)) {
            //$this->addCSSPopup('status_message');
            //$this->addScriptEvent("popupOpen('status_message_popup');");
        }
        $this->generateOnLogin();
        //$this->csspopups = $this->getCSSPopups();

        //$this->addBaseFolder('/usr/local/lib/php/master/webcomictv/', 0);
        //$this->addTemplateDirectory('templates');
        
        return true;
    }


    public function site_captcha($height=30, $width=120, $color=false, $aFonts=array('../fonts/VeraBd.ttf', '../fonts/VeraIt.ttf', '../fonts/Vera.ttf')) {
        $oVisualCaptcha = new PhpCaptcha($aFonts, $width, $height);
        $oVisualCaptcha->UseColour($color);
        $oVisualCaptcha->Create();
    }

    public function getPagination($f_page_number=1, $f_show_per_page=25, $f_total=false, $f_display=10) {
        $p['current'] = $f_page_number;

        $p['start'] = (($f_page_number - 1) * $f_show_per_page);
        $p['end'] = $p['start'] + $f_show_per_page;
        if ($p['end'] > $f_total) {
            $p['end'] = $f_total;
        }
        $p['last'] = ceil($f_total / $f_show_per_page);
        if ($f_page_number + 1 <= $p['last']) {
            $p['next'] = $f_page_number + 1;
            $p['lastshow'] = true;
        }
        if ($f_page_number - 1 >= 1) {
            $p['previous'] = $f_page_number - 1;
            $p['first'] = true;
        }
        $p['startshow'] = $p['start'] + 1;

        $range = floor($f_display / 2);
        //If more than one page.
        if ($p['last'] > 1) {
            //If last page is less than the display count.
            if ($p['last'] < $f_display) {
                for ($a = 1; $a <= $p['last']; $a++) {
                    $p['range'][] = $a;
                    $p['lastshow'] = false;
                }
                //If last page is greater than display count.
            } else {
                //If the number of total pages is less than display count.
                if (($f_page_number + $range) < $f_display) {
                    for ($a = 1; $a <= $f_display; $a++) {
                        $p['range'][] = $a;
                        $p['startshow'] = false;
                    }

                    //If the number of total pages is greater than display count.
                } else {
                    //If the number of shown pages is greater than last page.
                    if (($f_page_number + $f_display) > $p['last']) {
                        for ($a = ($p['last'] - $f_display) + 1; $a <= $p['last']; $a++) {
                            $p['range'][] = $a;
                            $p['lastshow'] = false;
                        }
                        //If the number of shown pages is less than last page.
                    } else {
                        $left = $f_page_number + $range;
                        for ($a = ($f_page_number - $range); $a <= $left; $a++) {
                            if ($a < 1) {
                                $left++;
                            } else {
                                $p['range'][] = $a;
                            }
                        }
                    }
                }
            }
        }
        return $p;
    }


}

?>