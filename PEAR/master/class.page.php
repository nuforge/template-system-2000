<?php
class page {

	public $tpldirs = array('default');
	
	public function __construct($site=false) {
		if(!empty($site)){$this->site=$site;}
		if (method_exists($this, 'localInitialize')) {$this->localInitialize();}
		if (method_exists($this, 'globalInitialize')) {$this->globalInitialize();}
		if (method_exists($this, 'localReinitialize')) {$this->localReinitialize();}
	}
	
	public function outputPrepartion() {
		return true;
	}
		
	protected function findTemplate ($name, $dirs, $base='../templates/') {
		while (!empty($dirs) && $dir = $base . array_pop($dirs)) {
			if(file_exists($dir . '/' . $name . '.tpl')) { return $dir;}
		}
		return false;
	}

	public function output ($name='main', $tpldir=false) {
		$this->outputPrepartion();
		if ($tpldir){$this->tpldir = $tpldir;} else {if(!($this->tpldir = $this->findTemplate($name, $this->tpldirs))) {return false;}}
		$tpl = new HTML_Template_Flexy(array('templateDir'=>$this->tpldir,'compileDir'=>'/tmp/flexy_compiled_templates/'));
		$tpl->compile($name . '.tpl');
		if (isset ($this->elements) ) {$tpl->outputObject($this, $this->elements);} else {$tpl->outputObject($this);}
	}
	
	public function display ($pg) {
		$this->pg = $pg;
		if (method_exists($this, 'pg_'.$pg)) {call_user_func(array($this, 'pg_'.$pg));} else { return $this->output();}
	}
	
	public function errors ($show = true) {
		ini_set("display_errors" , $show);
	}
	
	public function fillDates ($years=13,$end=90) {
		for ($a=1; $a<=31; $a++) {$t_array['days'][str_pad($a, 2, "0", STR_PAD_LEFT)]=$a;}
		for ($a=1; $a<=12; $a++) {$t_array['months'][str_pad($a, 2, "0", STR_PAD_LEFT)]=date('M', mktime(0,0,0,$a,1));}
		for ($a=date('Y')-$years; $a>=date('Y') - $end; $a--) {$t_array['years'][$a] = $a;}
		return $t_array;
	}
	
	
	public function split_date($string,$prefix='b') {
		$keys = array($prefix.'y',$prefix.'m',$prefix.'d');
		$values = explode('-',$string);
		return array_combine($keys, $values);
	}
	
	public function implodeArray($pieces,$glue) {
		return implode($glue,$pieces);
	}
	
	public function storeValue ($title,$value) {
		$this->$title = $value;
	}
	public function addValue ($title,$value) {
		$this->$title += $value;
	}
	public function subValue ($title,$value) {
		$this->$title -= $value;
	}
	
	public function appendValue ($title,$value) {
		$this->$title .= $value;
	}
	public function fillForms ($array) {
		foreach ($array as $key=>$val) {if (empty($this->elements[$key])) {$this->elements[$key] = new HTML_Template_Flexy_Element;} $this->elements[$key]->setValue($val);}
	}
	
	public function apTitle ($title) {
		if (empty($this->page['title'])) {$this->page['title']= $title; } else {$this->page['title'] = $title . ' ' . $this->page['title'];}
		return $this->page['title'];
	}
	
	public function apDescription ($description,$join=' - ') {
		if (empty($this->page['description'])) {$this->page['description']= $description; } else {$this->page['description'] = $description . $join . $this->page['description'];}
		return 	$this->page['description'];
	}
	
	public function adKeywords ($keywords) {
		if (is_array($keywords)) {$keywords = implode(',',$keywords);}
		if (empty($this->page['keywords'])) {$this->page['keywords']= $keywords; } else {$this->page['keywords'] = $keywords . ',' . $this->page['keywords'];}
		return $this->page['keywords'];
	}
	
	public function encodeString ($string,$replace='-',$lower=true) {
		$text = preg_replace("[\-$]", '',preg_replace("[\W+]", $replace, $string));
		if($lower) {
			return strtolower($text);
		}
		return $text;
	}
	
	public function dateFormat ($format, $date=false) {
		$var =  ($date) ? date($format, strtotime($date)) : date($format); 
		return $var;
	}
	
	public function truncate ($string, $length, $cap='...', $start=0) {
		if (strlen($string) > $length) {
			return substr($string, $start, $length) . $cap;
		} else {
			return $string;
		}
	}
	
	public function getDomain ($url = false) {
		if (empty($url)) { $url = $_SERVER['HTTP_HOST'];}
		$count = (count(explode(".", $url )) - 1);
		if (ereg("\co\.",  $url )){$count--;}
		$domain=preg_replace("/([^\.]+)\./i", "",  $url ,$count-1);
		
		return $domain;
	}
	
	public function mathAdd ($num1,$num2=1) {
		return $num1+$num2;	
	}
	
	public function mathMultiply ($num1,$num2=1) {
		return $num1*$num2;	
	}
	
	public function mathSub ($num1,$num2=1) {
		return $num1-$num2;	
	}
	
	public function mathDivide ($num1,$num2=1) {
		if ($num2 == 0) { return false;}
		return $num1/$num2;	
	}
	
	public function mathRound($num,$round=2) {
		return round($num,$round);
	}
	
	
	public function mathMod ($integer,$mod=2,$plus=0) {
		if (($integer + $plus) % $mod) {return false;} else {return true;}
	}
	
	public function compareLessThan ($num1,$num2,$andEqual = false) {
		if($andEqual) {	return $num1 <= $num2; } else {	return $num1 < $num2; }
	}
	public function compareGreaterThan ($num1,$num2,$andEqual = false) {
		if($andEqual) {	return $num1 >= $num2; } else {	return $num1 > $num2; }
	}
	
	public function rowMod ($integer,$mod=2,$plus=1,$string="row") {
		return $string . (($integer % $mod) + $plus);
	}
	
	public function modRow ($integer,$mod=2,$plus=1,$string="row") {
		return $string . (($integer % $mod) + $plus);
	}
	
	public function text2html ($text,$autolink=false,$paragraph=true) {
		$text = str_replace ('\r\n\r\n', '</p><p>',$text);
		$text = nl2br($text);
		if ($autolink) {$text = $this->hyperlink($text);}
		if($paragraph) {
			return '<p>' . $text . '</p>';
		} else {
			return $text;		
		}
	}
	
	public function text2html2  ($text,$autolink=false,$paragraph=true) {
		$pattern = '/\n\n/';
		$text = preg_replace ($pattern, '</p><p>',$text);
		$text = nl2br($text);
		if ($autolink) {$text = $this->hyperlink($text);}
		if($paragraph) {
			return '<p>' . $text . '</p>';
		} else {
			return $text;		
		}
	}	
	
	function hyperlink($text) {
		 // match protocol://address/path/file.extension?some=variable&another=asf%
		 $text = preg_replace("/([a-zA-Z]+:\/\/[a-z0-9\_\.\-]+[a-z]{2,6}[a-zA-Z0-9\/\*\-\?\_\&\%\=\,\.]+)/","<a href=\"$1\">$1</a>", $text);
		 // match www.something.domain/path/file.extension?some=variable&another=asf%
		// $text = preg_replace("/[^a-z]+[^:\/\/](www\.[^\.]+[\w][\.|\/][a-zA-Z0-9\/\*\-\?\&\%\=\,\.]+)/"," <a href=\"http://$1\" target=\"_blank\">$1</a>", $text);
		 // match name@address + ?subject etc...
		//$text = preg_replace("/([\s|\,\>])([a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]*\@[a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]{2,6})([A-Za-z0-9\!\?\@\#\$\%\^\&\*\(\)\_\-\=\+]*)([\s|\.|\,\<])/i","$1<a href=\"mailto:$2$3\">$2</a>$4",$text);
		return $text;
 }
	
	public function checkMod ($integer,$mod=2,$plus=0) {
		if (($integer + $plus) % $mod) {return false;} else {return true;}
	}
	
	public function compare($value1,$value2,$not=false,$exact=false) {
		if (!$exact) {
			if (!$not) {
				return ($value1 == $value2);
			} else {
				return ($value1 != $value2);
			}
		} else {
			if (!$not) {
				return ($value1 === $value2);
			} else {
				return ($value1 !== $value2);
			}
		}	
	}
	
	public function bbcode ($text) {
		$parser = new HTML_BBCodeParser();
		$parser->setText(strip_tags($text));
		$parser->addfilters('links,lists,basic,forums');
		$parser->parse();
		return $this->text2html($parser->getParsed());
	}
	
	public function pg_captcha ($height=50,$width=150,$color=false,$aFonts=array('../fonts/VeraBd.ttf', '../fonts/VeraIt.ttf', '../fonts/Vera.ttf')) {
		$oVisualCaptcha = new PhpCaptcha($aFonts, $width, $height);
		$oVisualCaptcha->UseColour($color);
		$oVisualCaptcha->Create();
	}
	
	public function numberFormat($num,$dec=0,$ds='.',$ts=',') {
		return number_format($num,$dec,$ds,$ts);
	}
	
}
?>