<?php

class page extends flexy_page {

    public $myCSSPopups = array();

    public function errors($show = true) {
        ini_set("display_errors", $show);
    }

    public function addCSSPopup($f_popup_name,$f_prefix='popup_',$f_suffix='') {
        $this->myCSSPopups[] = $f_prefix.$f_popup_name.$f_suffix;
    }


    public function removeCSSPopup($f_popup_name,$f_suffix='_popup') {
        $key = array_search($f_popup_name.$f_suffix, $this->myCSSPopups);
        unset($this->myCSSPopups[$key]);
    }


    public function getCSSPopups() {
        return $this->myCSSPopups;
    }


    public function outputCSSPopup($f_popup_name,$f_suffix='_popup') {
        $this->output($f_popup_name.$f_suffix);
    }

    

    public function fillDates($years=13, $end=90) {
        for ($a = 1; $a <= 31; $a++) {
            $t_array['days'][str_pad($a, 2, "0", STR_PAD_LEFT)] = $a;
        }
        for ($a = 1; $a <= 12; $a++) {
            $t_array['months'][str_pad($a, 2, "0", STR_PAD_LEFT)] = date('M', mktime(0, 0, 0, $a, 1));
        }
        for ($a = date('Y') - $years; $a >= date('Y') - $end; $a--) {
            $t_array['years'][$a] = $a;
        }
        return $t_array;
    }

    public function split_date($string, $prefix='b') {
        $keys = array($prefix . 'y', $prefix . 'm', $prefix . 'd');
        $values = explode('-', $string);
        return array_combine($keys, $values);
    }

    public function getDomain($url = false) {
        if (empty($url)) {
            $url = $_SERVER['HTTP_HOST'];
        }
        $count = (count(explode(".", $url)) - 1);
        if (ereg("\co\.", $url)) {
            $count--;
        }
        $domain = preg_replace("/([^\.]+)\./i", "", $url, $count - 1);

        return $domain;
    }

    public function apTitle($f_title, $f_seperator='|') {
        return $this->appendTitle($f_title, $f_seperator);
    }

    public function apDescription($f_description, $f_seperator=' - ') {
        return $this->appendDescription($f_description, $f_seperator);
    }

    public function adKeywords($f_keywords) {
        return $this->addKeywords($f_keywords);
    }
    
    public function generateOnLogin () {

        if (!$this->exclude_from_redirect && $_SERVER["REDIRECT_STATUS"] == '200') {
            $_SESSION['REDIRECT_URL'] = $_SERVER['REQUEST_URI'];
        }
    }

    public function initializeMember() {
        return true;
    }

    public function site_captcha() {
        $this->generateCaptcha();
    }
    
    public function generateCaptcha($height=50, $width=150, $color=false, $aFonts=array('../fonts/VeraBd.ttf', '../fonts/VeraIt.ttf', '../fonts/Vera.ttf')) {
        $oVisualCaptcha = new PhpCaptcha($aFonts, $width, $height);
        $oVisualCaptcha->UseColour($color);
        $oVisualCaptcha->Create();
    }

    public function getPagination($f_page_number=1, $f_show_per_page=25, $f_total=false, $f_display=5) {
        if(!$f_show_per_page) { $f_show_per_page = 1;}
        if (empty($f_total)) {
            return false;
        }
        $p['current'] = $f_page_number;
        if ($f_show_per_page >= $f_total) {
            return $p;
        }

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
        if ($range < 1) {
            $range = 1;
        }
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

        if ($p['range'][0] == 1) {
            $p['first'] = false;
        }


        if ($p['range'][count($p['range']) - 1] == $p['last']) {
            $p['lastshow'] = false;
        }

        return $p;
    }

    // FLEXY RETURN FUNCTIONS

    public function numberFormat($f_num, $f_dec=0, $f_ds='.', $f_ts=',') {
        return $this->flexy_numberFormat($f_num, $f_dec, $f_ds, $f_ts);
    }

    public function implodeArray($f_pieces, $f_glue=', ') {
        return $this->flexy_implodeArray($f_pieces, $f_glue);
    }

    public function encodeString($f_string, $f_replace='-', $f_lower=true) {
        return $this->flexy_encodeString($f_string, $f_replace, $f_lower);
    }

    public function dateFormat($f_format, $f_date=false) {
        return $this->flexy_dateFormat($f_format, $f_date);
    }

    public function rowMod($f_integer, $f_mod=2, $f_plus=1, $f_string="row") {
        return $this->flexy_rowMod($f_integer, $f_mod, $f_plus, $f_string);
    }

    public function modRow($f_integer, $f_mod=2, $f_plus=1, $f_string="row") {
        return $this->flexy_rowMod($f_integer, $f_mod, $f_plus, $f_string);
    }

    public function storeValue($f_variable_name, $f_new_value, $f_return= false) {
        return $this->flexy_storeValue($f_variable_name, $f_new_value, $f_return);
    }

    public function addValue($f_variable_name, $f_new_value, $f_return= false) {
        $this->flexy_addValue($f_variable_name, $f_new_value, $return);
    }

    public function subValue($f_variable_name, $f_new_value, $f_return= false) {
        $this->flexy_subtractValue($f_variable_name, $f_new_value, $f_return);
    }

    public function subtractValue($f_variable_name, $f_new_value, $f_return= false) {
        return $this->flexy_subtractValue($f_variable_name, $f_new_value, $f_return);
    }

    public function appendValue($f_variable_name, $f_new_value, $f_return= false) {
        return $this->flexy_appendValue($f_variable_name, $f_new_value, $f_return);
    }

    public function truncate($f_string_to_truncate, $f_cut_to_length, $f_add_to_end='', $f_start_position=0) {
        return $this->flexy_truncateString($string, $length, $cap, $start);
    }

    public function mathAdd($f_number_1, $f_number_2=1) {
        return $this->flexy_mathAdd($f_number_1, $f_number_2);
    }

    public function mathMultiply($f_number_1, $f_number_2=1) {
        return $this->flexy_mathMultiply($f_number_1, $f_number_2);
    }

    public function mathSub($f_number_1, $f_number_2=1) {
        return $this->flexy_mathSubtract($f_number_1, $f_number_2);
    }

    public function mathSubtract($f_number_1, $f_number_2=1) {
        return $this->flexy_mathSubtract($f_number_1, $f_number_2);
    }

    public function mathDivide($f_number_1, $f_number_2=1) {
        return $this->flexy_mathDivide($f_number_1, $f_number_2);
    }

    public function mathRound($f_number, $f_decimal_places=2) {
        return $this->flexy_mathDivide($f_number, $f_decimal_places);
    }

    public function mathMod($f_number, $f_mod=2, $f_plus=0) {
        return $this->flexy_mathMod($f_number, $f_mod, $f_plus);
    }

    public function checkMod($f_number, $f_mod_by=2, $f_plus=0) {
        return $this->flexy_checkMod($f_number, $f_mod_by, $f_plus);
    }

    public function compareLessThan($f_number_1, $f_number_2, $f_and_equal = false) {
        return $this->flexy_compareLessThan($f_number_1, $f_number_2, $f_and_equal);
    }

    public function compareGreaterThan($f_number_1, $f_number_2, $f_and_equal = false) {
        return $this->flexy_compareGreaterThan($f_number_1, $f_number_2, $f_and_equal);
    }

    public function text2html($f_string, $f_autolink=false, $f_paragraph=true) {
        return $this->flexy_text2html($f_string, $f_autolink, $f_paragraph);
    }

    public function text2html2($f_string, $f_autolink=false, $f_paragraph=true) {
        return $this->flexy_text2html2($f_string, $f_autolink, $f_paragraph);
    }

    public function hyperlink($f_string) {
        return $this->flexy_hyperlink($f_string);
    }

    public function compare($f_value_1, $f_value_2, $f_not_equal=false, $f_is_exact=false) {
        return $this->flexy_compareValues($f_value_1, $f_value_2, $f_not_equal, $f_is_exact);
    }

    public function bbcode($f_string) {
        return $this->flexy_convertToBBCode($f_string);
    }

}

?>